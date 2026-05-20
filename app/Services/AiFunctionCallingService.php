<?php

namespace App\Services;

use App\Models\AiFunction;
use App\Models\Contact;
use App\Models\CustomField;
use App\Models\CustomFieldValue;
use App\Models\PipelineStage;
use App\Models\WhatsappAiAssistant;
use App\Models\WhatsappConversation;
use Illuminate\Support\Facades\Log;

class AiFunctionCallingService
{
    /**
     * Construye el array de tools para OpenAI a partir de las funciones IA
     * configuradas por el usuario en la BD.
     */
    public function buildTools(WhatsappAiAssistant $assistant): array
    {
        $functions = AiFunction::where('whatsapp_ai_assistant_id', $assistant->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $tools = [];

        foreach ($functions as $fn) {
            $params = [
                'type'       => 'object',
                'properties' => [],
                'required'   => [],
            ];

            // Para update_crm: agregar cada propiedad seleccionada
            if ($fn->mode === AiFunction::MODE_UPDATE_CRM && is_array($fn->properties)) {
                foreach ($fn->properties as $prop) {
                    $schema = $this->propertySchema($prop, $fn->team_id);
                    if (!$schema) continue;
                    $params['properties'][$schema['key']] = $schema['def'];
                    if (!empty($schema['required'])) $params['required'][] = $schema['key'];
                }
            }

            // change_stage no necesita parámetros (la fase está predefinida)
            // info no necesita parámetros

            // Si no quedaron requeridos, quitar la clave para que OpenAI no se queje
            if (empty($params['required'])) unset($params['required']);

            $tools[] = [
                'type'     => 'function',
                'function' => [
                    'name'        => $fn->name,
                    'description' => $fn->description,
                    'parameters'  => empty($params['properties'])
                        ? ['type' => 'object', 'properties' => new \stdClass()]
                        : $params,
                ],
            ];
        }

        return $tools;
    }

    /**
     * Ejecuta una llamada a herramienta y devuelve un mensaje resultado.
     * Devuelve también el "follow-up" del bot a enviar (si la función tiene response_template).
     *
     * @return array{result: array, response?: string|null}
     */
    public function executeToolCall(string $name, array $args, WhatsappConversation $conversation, ?int $assistantId = null): array
    {
        try {
            // Resolver assistantId: parámetro explícito > lookup desde la conversación
            if (!$assistantId) {
                $assistantId = $conversation->account?->aiAssistant?->id;
            }

            if (!$assistantId) {
                Log::warning("AI executeToolCall sin assistant_id", ['name' => $name]);
                return ['result' => ['ok' => false, 'message' => 'Asistente no encontrado']];
            }

            $fn = AiFunction::where('whatsapp_ai_assistant_id', $assistantId)
                ->where('name', $name)
                ->where('is_active', true)
                ->first();

            if (!$fn) {
                Log::warning("AI función no encontrada", ['name' => $name, 'assistant' => $assistantId]);
                return ['result' => ['ok' => false, 'message' => "Función '{$name}' no encontrada o inactiva"]];
            }

            switch ($fn->mode) {
                case AiFunction::MODE_UPDATE_CRM:
                    $r = $this->runUpdateCrm($fn, $args, $conversation);
                    break;
                case AiFunction::MODE_CHANGE_STAGE:
                    $r = $this->runChangeStage($fn, $conversation);
                    break;
                case AiFunction::MODE_INFO:
                    $r = ['ok' => true, 'message' => 'Respuesta informativa'];
                    break;
                default:
                    $r = ['ok' => false, 'message' => "Modo desconocido: {$fn->mode}"];
            }

            return [
                'result'   => $r,
                'response' => $fn->response_template ?: null,
            ];
        } catch (\Throwable $e) {
            Log::error("AI function '{$name}' falló: " . $e->getMessage(), [
                'trace' => substr($e->getTraceAsString(), 0, 500),
            ]);
            return ['result' => ['ok' => false, 'message' => 'Error: ' . $e->getMessage()]];
        }
    }

    /* ============ Modos ============ */

    private function runUpdateCrm(AiFunction $fn, array $args, WhatsappConversation $conversation): array
    {
        $contact = $this->resolveContact($conversation);
        $deal    = $this->resolveDeal($conversation);

        Log::info('AI runUpdateCrm', [
            'function'   => $fn->name,
            'properties' => $fn->properties,
            'args'       => $args,
            'contact_id' => $contact?->id,
            'deal_id'    => $deal?->id,
            'conv_id'    => $conversation->id,
        ]);

        if (!$contact && !$deal) {
            return ['ok' => false, 'message' => 'No se encontró contacto ni negociación para actualizar.'];
        }

        $changed  = [];
        $skipped  = [];

        foreach ((array) $fn->properties as $prop) {
            $key = $this->propertyKey($prop);

            // Buscar valor con varias estrategias (por si OpenAI envía la clave distinta)
            $value = null;
            if ($key && array_key_exists($key, $args)) {
                $value = $args[$key];
            } elseif (array_key_exists($prop, $args)) {
                $value = $args[$prop]; // formato literal "contact.name"
            } else {
                // Buscar por el último segmento (ej: para contact.name busca "name")
                $segments = explode('.', $prop);
                $last     = end($segments);
                if ($last && array_key_exists($last, $args)) {
                    $value = $args[$last];
                }
            }

            if ($value === null) {
                $skipped[] = "$prop (no en args)";
                continue;
            }
            if (is_string($value)) $value = trim($value);
            if ($value === '' || $value === null) {
                $skipped[] = "$prop (valor vacío)";
                continue;
            }

            // Standard contact field
            if (str_starts_with($prop, 'contact.')) {
                $field = substr($prop, 8);
                if (!$contact) {
                    $skipped[] = "$prop (sin contacto)";
                    continue;
                }
                if (!in_array($field, $this->allowedContactFields(), true)) {
                    $skipped[] = "$prop (campo no permitido)";
                    continue;
                }
                try {
                    $contact->update([$field => $value]);
                    $changed[] = "contact.{$field}={$value}";
                } catch (\Throwable $e) {
                    Log::error("Falló contact->update {$field}: " . $e->getMessage());
                    $skipped[] = "$prop (error DB)";
                }
                continue;
            }

            // Standard deal field
            if (str_starts_with($prop, 'deal.')) {
                $field = substr($prop, 5);
                if (!$deal) {
                    $skipped[] = "$prop (sin deal abierto)";
                    continue;
                }
                if (!in_array($field, $this->allowedDealFields(), true)) {
                    $skipped[] = "$prop (campo no permitido)";
                    continue;
                }
                if ($field === 'amount') {
                    $value = (float) $value;
                } elseif ($field === 'close_date') {
                    try {
                        $value = \Carbon\Carbon::parse($value)->toDateString();
                    } catch (\Throwable $e) {
                        $value = null;
                    }
                }
                if ($value !== null && $value !== '') {
                    try {
                        $deal->update([$field => $value]);
                        $changed[] = "deal.{$field}={$value}";
                    } catch (\Throwable $e) {
                        Log::error("Falló deal->update {$field}: " . $e->getMessage());
                        $skipped[] = "$prop (error DB)";
                    }
                }
                continue;
            }

            // Custom field
            if (preg_match('/^custom\.(contact|deal)\.(\d+)$/', $prop, $m)) {
                $entity = $m[1];
                $cfId   = (int) $m[2];
                $cf = CustomField::where('id', $cfId)
                    ->where('team_id', $fn->team_id)
                    ->where('entity_type', $entity)
                    ->where('is_active', true)
                    ->first();
                if (!$cf) {
                    $skipped[] = "$prop (campo personalizado no encontrado)";
                    continue;
                }

                $target = $entity === 'contact' ? $contact : $deal;
                if (!$target) {
                    $skipped[] = "$prop (sin " . $entity . ")";
                    continue;
                }

                try {
                    CustomFieldValue::updateOrCreate(
                        [
                            'custom_field_id' => $cf->id,
                            'valuable_type'   => get_class($target),
                            'valuable_id'     => $target->getKey(),
                        ],
                        ['value' => (string) $value]
                    );
                    $changed[] = "custom.{$entity}.{$cf->slug}={$value}";
                } catch (\Throwable $e) {
                    Log::error("Falló customField update: " . $e->getMessage());
                    $skipped[] = "$prop (error DB)";
                }
            }
        }

        Log::info('AI runUpdateCrm resultado', [
            'changed' => $changed,
            'skipped' => $skipped,
        ]);

        return [
            'ok'      => !empty($changed),
            'message' => empty($changed)
                ? 'No se pudo actualizar nada. Skipped: ' . implode(', ', $skipped)
                : 'Actualizado: ' . implode(', ', $changed),
            'changed' => $changed,
            'skipped' => $skipped,
        ];
    }

    private function runChangeStage(AiFunction $fn, WhatsappConversation $conversation): array
    {
        $deal = $this->resolveDeal($conversation);
        if (!$deal) return ['ok' => false, 'message' => 'No hay negociación abierta'];
        if (!$fn->target_stage_id) return ['ok' => false, 'message' => 'Función sin fase configurada'];

        $stage = PipelineStage::where('id', $fn->target_stage_id)
            ->where('pipeline_id', $deal->pipeline_id)
            ->first();
        if (!$stage) return ['ok' => false, 'message' => 'Fase inválida'];

        $status = 'open';
        if ($stage->is_won)  $status = 'won';
        if ($stage->is_lost) $status = 'lost';

        $deal->update(['stage_id' => $stage->id, 'status' => $status]);
        return ['ok' => true, 'message' => "Fase cambiada a '{$stage->name}'"];
    }

    /* ============ Helpers ============ */

    private function resolveContact(WhatsappConversation $conversation): ?Contact
    {
        // 1) Primero por deal abierto
        $deal = $conversation->deals()
            ->where('status', 'open')
            ->orderByDesc('whatsapp_conversation_deals.created_at')
            ->first();
        if ($deal && $deal->contact_id) {
            $c = Contact::find($deal->contact_id);
            if ($c) return $c;
        }

        // 2) Por teléfono dentro del team
        $phone = $conversation->contact_phone ?? $conversation->wa_id;
        if ($phone) {
            $c = Contact::where('team_id', $conversation->team_id)
                ->where('phone', $phone)
                ->first();
            if ($c) return $c;
        }

        // 3) Crear uno si no existe
        try {
            return Contact::create([
                'team_id'    => $conversation->team_id,
                'owner_id'   => $conversation->account?->team?->owner_id ?? 1,
                'name'       => $conversation->contact_name ?? $phone ?? 'Contacto WA',
                'first_name' => $conversation->contact_name ?? $phone ?? 'Contacto WA',
                'phone'      => $phone,
                'status'     => 'nuevo',
                'source'     => 'whatsapp_ai',
            ]);
        } catch (\Throwable $e) {
            Log::error('No se pudo crear contacto desde IA: ' . $e->getMessage());
            return null;
        }
    }

    private function resolveDeal(WhatsappConversation $conversation): ?\App\Models\Deal
    {
        return $conversation->deals()
            ->where('status', 'open')
            ->orderByDesc('whatsapp_conversation_deals.created_at')
            ->first();
    }

    private function allowedContactFields(): array
    {
        return ['name', 'first_name', 'last_name', 'email', 'phone', 'company', 'position', 'tipo_doc', 'num_doc', 'razon_social', 'notes', 'status'];
    }

    private function allowedDealFields(): array
    {
        return ['title', 'amount', 'currency', 'close_date', 'description'];
    }

    /**
     * Devuelve la clave (parameter name) y schema para una property string.
     * Property formato:
     *   contact.<field>            → contact_<field>
     *   deal.<field>               → deal_<field>
     *   custom.contact.<id>        → custom_contact_<id>
     *   custom.deal.<id>           → custom_deal_<id>
     */
    private function propertySchema(string $prop, int $teamId): ?array
    {
        $key = $this->propertyKey($prop);
        if (!$key) return null;

        if (str_starts_with($prop, 'contact.')) {
            $field = substr($prop, 8);
            return ['key' => $key, 'def' => $this->contactFieldDef($field), 'required' => false];
        }
        if (str_starts_with($prop, 'deal.')) {
            $field = substr($prop, 5);
            return ['key' => $key, 'def' => $this->dealFieldDef($field), 'required' => false];
        }
        if (preg_match('/^custom\.(contact|deal)\.(\d+)$/', $prop, $m)) {
            $cf = CustomField::where('id', (int) $m[2])
                ->where('team_id', $teamId)
                ->first();
            if (!$cf) return null;
            return ['key' => $key, 'def' => $this->customFieldDef($cf), 'required' => false];
        }
        return null;
    }

    private function propertyKey(string $prop): ?string
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '_', $prop) ?: null;
    }

    private function contactFieldDef(string $field): array
    {
        $defs = [
            'name'         => ['type' => 'string', 'description' => 'Nombre completo del cliente'],
            'first_name'   => ['type' => 'string', 'description' => 'Nombre del cliente'],
            'last_name'    => ['type' => 'string', 'description' => 'Apellido del cliente'],
            'email'        => ['type' => 'string', 'description' => 'Correo electrónico'],
            'phone'        => ['type' => 'string', 'description' => 'Teléfono'],
            'company'      => ['type' => 'string', 'description' => 'Empresa'],
            'position'     => ['type' => 'string', 'description' => 'Cargo en la empresa'],
            'tipo_doc'     => ['type' => 'string', 'enum' => ['1', '6', '4'], 'description' => '1=DNI, 6=RUC, 4=CE'],
            'num_doc'      => ['type' => 'string', 'description' => 'Número de documento'],
            'razon_social' => ['type' => 'string', 'description' => 'Razón social (empresa con RUC)'],
            'notes'        => ['type' => 'string', 'description' => 'Notas adicionales'],
            'status'       => ['type' => 'string', 'description' => 'Estado del contacto (nuevo/activo/cliente/inactivo)'],
        ];
        return $defs[$field] ?? ['type' => 'string'];
    }

    private function dealFieldDef(string $field): array
    {
        $defs = [
            'title'       => ['type' => 'string', 'description' => 'Título de la negociación'],
            'amount'      => ['type' => 'number', 'description' => 'Monto estimado'],
            'currency'    => ['type' => 'string', 'enum' => ['PEN', 'USD', 'EUR']],
            'close_date'  => ['type' => 'string', 'description' => 'Fecha estimada de cierre (YYYY-MM-DD)'],
            'description' => ['type' => 'string', 'description' => 'Descripción / notas'],
        ];
        return $defs[$field] ?? ['type' => 'string'];
    }

    private function customFieldDef(CustomField $cf): array
    {
        $base = ['description' => $cf->name];
        switch ($cf->field_type) {
            case 'number':
                return $base + ['type' => 'number'];
            case 'date':
                return $base + ['type' => 'string', 'description' => $cf->name . ' (YYYY-MM-DD)'];
            case 'select':
                $opts = is_array($cf->options) ? array_values($cf->options) : [];
                return $base + ['type' => 'string', 'enum' => $opts ?: ['']];
            default:
                return $base + ['type' => 'string'];
        }
    }
}
