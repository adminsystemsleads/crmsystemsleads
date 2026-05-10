<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\CustomField;
use App\Models\Deal;
use App\Models\WhatsappAiAssistant;
use App\Models\WhatsappConversation;
use App\Support\CustomFieldsHelper;
use Illuminate\Support\Facades\Log;

class AiFunctionCallingService
{
    /**
     * Devuelve el array de tools (formato OpenAI) que puede usar el asistente,
     * basado en su configuración y los campos personalizados del team.
     */
    public function buildTools(WhatsappAiAssistant $assistant): array
    {
        $config = $assistant->capture_config ?? [];
        $captureContact = $config['contact'] ?? true;
        $captureDeal    = $config['deal']    ?? true;
        $captureCustom  = $config['custom']  ?? true;

        $tools = [];

        if ($captureContact) {
            $tools[] = [
                'type' => 'function',
                'function' => [
                    'name'        => 'save_contact_data',
                    'description' => 'Guarda o actualiza datos del contacto cuando el cliente los menciona durante la conversación. Solo úsalo cuando el cliente proporcione datos concretos.',
                    'parameters'  => [
                        'type' => 'object',
                        'properties' => [
                            'name'         => ['type' => 'string', 'description' => 'Nombre completo del cliente'],
                            'email'        => ['type' => 'string', 'description' => 'Correo electrónico'],
                            'company'      => ['type' => 'string', 'description' => 'Empresa donde trabaja'],
                            'position'     => ['type' => 'string', 'description' => 'Cargo o puesto'],
                            'tipo_doc'     => ['type' => 'string', 'enum' => ['1', '6', '4'], 'description' => 'Tipo de documento: 1=DNI, 6=RUC, 4=CE'],
                            'num_doc'      => ['type' => 'string', 'description' => 'Número del documento'],
                            'razon_social' => ['type' => 'string', 'description' => 'Razón social (solo si es empresa con RUC)'],
                        ],
                    ],
                ],
            ];
        }

        if ($captureDeal) {
            $tools[] = [
                'type' => 'function',
                'function' => [
                    'name'        => 'save_deal_data',
                    'description' => 'Actualiza datos de la negociación actual cuando el cliente comparte información relevante (presupuesto, fecha tentativa de cierre, descripción del proyecto).',
                    'parameters'  => [
                        'type' => 'object',
                        'properties' => [
                            'title'       => ['type' => 'string', 'description' => 'Título o resumen de la oportunidad'],
                            'amount'      => ['type' => 'number', 'description' => 'Monto estimado'],
                            'currency'    => ['type' => 'string', 'enum' => ['PEN', 'USD', 'EUR']],
                            'close_date'  => ['type' => 'string', 'description' => 'Fecha estimada de cierre en formato YYYY-MM-DD'],
                            'description' => ['type' => 'string', 'description' => 'Notas / detalles del proyecto del cliente'],
                        ],
                    ],
                ],
            ];
        }

        if ($captureCustom) {
            $customFields = CustomFieldsHelper::fieldsFor($assistant->team_id, 'contact')
                ->concat(CustomFieldsHelper::fieldsFor($assistant->team_id, 'deal'));

            if ($customFields->isNotEmpty()) {
                $allowedSlugs = $customFields->map(fn($cf) => $cf->entity_type . '.' . $cf->slug)->all();

                $description = "Guarda un valor para un campo personalizado. Slugs disponibles:\n";
                foreach ($customFields as $cf) {
                    $extra = '';
                    if ($cf->field_type === 'select' && is_array($cf->options)) {
                        $extra = ' (opciones: ' . implode(', ', $cf->options) . ')';
                    } elseif ($cf->field_type === 'date') {
                        $extra = ' (fecha YYYY-MM-DD)';
                    } elseif ($cf->field_type === 'number') {
                        $extra = ' (número)';
                    }
                    $description .= "- {$cf->entity_type}.{$cf->slug}: {$cf->name}{$extra}\n";
                }

                $tools[] = [
                    'type' => 'function',
                    'function' => [
                        'name'        => 'save_custom_field',
                        'description' => trim($description),
                        'parameters'  => [
                            'type' => 'object',
                            'properties' => [
                                'field_slug' => ['type' => 'string', 'enum' => $allowedSlugs, 'description' => 'Identificador del campo personalizado en formato entity.slug'],
                                'value'      => ['type' => 'string', 'description' => 'Valor a guardar (texto)'],
                            ],
                            'required' => ['field_slug', 'value'],
                        ],
                    ],
                ];
            }
        }

        return $tools;
    }

    /**
     * Ejecuta una llamada a herramienta y devuelve un mensaje resultado para enviar de vuelta al modelo.
     */
    public function executeToolCall(string $name, array $args, WhatsappConversation $conversation): array
    {
        try {
            switch ($name) {
                case 'save_contact_data':
                    return $this->saveContactData($conversation, $args);
                case 'save_deal_data':
                    return $this->saveDealData($conversation, $args);
                case 'save_custom_field':
                    return $this->saveCustomField($conversation, $args);
                default:
                    return ['ok' => false, 'message' => "Herramienta desconocida: {$name}"];
            }
        } catch (\Throwable $e) {
            Log::error("AI tool '{$name}' falló: " . $e->getMessage());
            return ['ok' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    private function saveContactData(WhatsappConversation $conversation, array $args): array
    {
        $contact = $this->resolveContact($conversation);
        if (!$contact) return ['ok' => false, 'message' => 'No se encontró/creó contacto.'];

        $allowed = ['name', 'email', 'company', 'position', 'tipo_doc', 'num_doc', 'razon_social'];
        $update  = [];
        foreach ($allowed as $k) {
            if (isset($args[$k]) && trim((string) $args[$k]) !== '') {
                $update[$k] = trim((string) $args[$k]);
            }
        }

        if (!$update) return ['ok' => true, 'message' => 'Sin datos nuevos para actualizar.'];

        $contact->update($update);
        return ['ok' => true, 'message' => 'Contacto actualizado.', 'fields' => array_keys($update)];
    }

    private function saveDealData(WhatsappConversation $conversation, array $args): array
    {
        $deal = $conversation->deals()
            ->where('status', 'open')
            ->orderByDesc('whatsapp_conversation_deals.created_at')
            ->first();

        if (!$deal) return ['ok' => false, 'message' => 'No hay negociación abierta vinculada a esta conversación.'];

        $update = [];
        if (!empty($args['title']))       $update['title']       = trim((string) $args['title']);
        if (isset($args['amount']))       $update['amount']      = (float) $args['amount'];
        if (!empty($args['currency']))    $update['currency']    = strtoupper(substr((string) $args['currency'], 0, 3));
        if (!empty($args['description'])) $update['description'] = trim((string) $args['description']);
        if (!empty($args['close_date'])) {
            try { $update['close_date'] = \Carbon\Carbon::parse($args['close_date'])->toDateString(); }
            catch (\Throwable $e) { /* ignore bad date */ }
        }

        if (!$update) return ['ok' => true, 'message' => 'Sin datos nuevos.'];

        $deal->update($update);
        return ['ok' => true, 'message' => 'Negociación actualizada.', 'fields' => array_keys($update)];
    }

    private function saveCustomField(WhatsappConversation $conversation, array $args): array
    {
        $slug  = $args['field_slug'] ?? '';
        $value = (string) ($args['value'] ?? '');

        if (!$slug || $value === '') return ['ok' => false, 'message' => 'Faltan parámetros'];

        // El AI envía formato "entity.slug"
        [$entity, $fieldSlug] = array_pad(explode('.', $slug, 2), 2, null);
        if (!in_array($entity, ['contact', 'deal'], true) || !$fieldSlug) {
            return ['ok' => false, 'message' => 'Slug inválido'];
        }

        $cf = CustomField::where('team_id', $conversation->team_id)
            ->where('entity_type', $entity)
            ->where('slug', $fieldSlug)
            ->where('is_active', true)
            ->first();

        if (!$cf) return ['ok' => false, 'message' => "Campo {$slug} no encontrado"];

        $target = $entity === 'contact'
            ? $this->resolveContact($conversation)
            : $conversation->deals()->where('status', 'open')->orderByDesc('whatsapp_conversation_deals.created_at')->first();

        if (!$target) return ['ok' => false, 'message' => 'No se encontró el modelo destino'];

        \App\Models\CustomFieldValue::updateOrCreate(
            [
                'custom_field_id' => $cf->id,
                'valuable_type'   => get_class($target),
                'valuable_id'     => $target->getKey(),
            ],
            ['value' => $value]
        );

        return ['ok' => true, 'message' => "Campo '{$cf->name}' guardado"];
    }

    private function resolveContact(WhatsappConversation $conversation): ?Contact
    {
        $deal = $conversation->deals()->latest()->first();
        if ($deal && $deal->contact_id) {
            return Contact::find($deal->contact_id);
        }

        // Si no hay contacto, lo creamos con el teléfono de la conversación
        return Contact::firstOrCreate(
            ['team_id' => $conversation->team_id, 'phone' => $conversation->contact_phone],
            [
                'owner_id'   => $conversation->account?->team?->owner_id ?? 1,
                'name'       => $conversation->contact_name ?? $conversation->contact_phone ?? 'Contacto WA',
                'first_name' => $conversation->contact_name ?? $conversation->contact_phone ?? 'Contacto WA',
                'status'     => 'nuevo',
                'source'     => 'whatsapp_ai',
            ]
        );
    }
}
