<?php

namespace App\Services;

use App\Models\WhatsappAccount;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappTemplateService
{
    private const GRAPH = 'https://graph.facebook.com/v20.0';

    /**
     * Lista plantillas aprobadas de la WABA. Cachea 5 minutos.
     */
    public function listTemplates(WhatsappAccount $account, bool $force = false): array
    {
        if (!$account->waba_id) {
            return ['ok' => false, 'message' => 'La cuenta no tiene WABA ID configurado.', 'templates' => []];
        }

        $cacheKey = "wa_templates:{$account->id}";
        if ($force) Cache::forget($cacheKey);

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($account) {
            try {
                $res = Http::withToken($account->access_token)
                    ->acceptJson()
                    ->timeout(20)
                    ->get(self::GRAPH . "/{$account->waba_id}/message_templates", [
                        'limit'  => 200,
                        'fields' => 'name,language,status,category,components,id',
                    ]);

                if (!$res->successful()) {
                    $body = $res->json() ?? [];
                    $metaMsg = $body['error']['message'] ?? 'No se pudieron obtener las plantillas.';

                    // Errores típicos: WABA ID incorrecto o token sin permisos
                    if (str_contains($metaMsg, 'message_templates') || str_contains($metaMsg, 'nonexisting field')) {
                        $metaMsg = 'El WABA ID configurado no es válido (no es una WhatsApp Business Account). '
                                 . 'Verifica que en "WhatsApp → Cuentas → Editar" hayas puesto el WABA ID correcto '
                                 . '(NO el Phone Number ID). El WABA ID lo encuentras en Meta Business Suite → Cuentas de WhatsApp.';
                    } elseif (str_contains(strtolower($metaMsg), 'permission')) {
                        $metaMsg = 'El Access Token no tiene permiso "whatsapp_business_management". '
                                 . 'Genera un token con ese permiso en Meta para Desarrolladores.';
                    }

                    return [
                        'ok'        => false,
                        'message'   => $metaMsg,
                        'templates' => [],
                    ];
                }

                $data = $res->json('data') ?? [];

                // Solo aprobadas
                $approved = array_values(array_filter($data, fn($t) => ($t['status'] ?? '') === 'APPROVED'));

                return ['ok' => true, 'templates' => $approved];
            } catch (\Throwable $e) {
                Log::error('WA listTemplates error: ' . $e->getMessage());
                return ['ok' => false, 'message' => $e->getMessage(), 'templates' => []];
            }
        });
    }

    /**
     * Resuelve el WABA ID real a partir del Phone Number ID y lo persiste en la cuenta.
     * Retorna el WABA ID resuelto o null.
     */
    public function resolveWabaId(WhatsappAccount $account): ?string
    {
        if (!$account->phone_number_id || !$account->access_token) {
            return null;
        }

        try {
            $res = Http::withToken($account->access_token)
                ->acceptJson()->timeout(15)
                ->get(self::GRAPH . "/{$account->phone_number_id}", [
                    'fields' => 'whatsapp_business_account_id,verified_name,display_phone_number',
                ]);

            $wabaId = $res->json('whatsapp_business_account_id');
            if ($res->successful() && $wabaId) {
                if ((string) $account->waba_id !== (string) $wabaId) {
                    $account->forceFill(['waba_id' => $wabaId])->saveQuietly();
                }
                return (string) $wabaId;
            }
        } catch (\Throwable $e) {
            Log::error('WA resolveWabaId error: ' . $e->getMessage());
        }

        return null;
    }

    /** Realiza la consulta cruda de plantillas contra un WABA ID. */
    private function fetchTemplates(WhatsappAccount $account, string $wabaId): array
    {
        $res = Http::withToken($account->access_token)
            ->acceptJson()->timeout(20)
            ->get(self::GRAPH . "/{$wabaId}/message_templates", [
                'limit'  => 200,
                'fields' => 'name,language,status,category,components,id,rejected_reason',
            ]);

        return ['res' => $res, 'body' => $res->json() ?? []];
    }

    /**
     * Lista TODAS las plantillas (cualquier estado) para la pantalla de gestión.
     * Si el WABA ID configurado es inválido, intenta auto-resolverlo desde el Phone Number ID.
     */
    public function listAll(WhatsappAccount $account): array
    {
        if (!$account->access_token) {
            return ['ok' => false, 'message' => 'La cuenta no tiene Access Token configurado.', 'templates' => []];
        }

        try {
            $wabaId = $account->waba_id;

            // Si no hay WABA o falla con "nonexisting field", intentamos auto-resolverlo.
            if (!$wabaId) {
                $wabaId = $this->resolveWabaId($account);
                if (!$wabaId) {
                    return ['ok' => false, 'message' => 'No se pudo determinar el WABA ID. Verifica el Phone Number ID y que el token tenga permiso whatsapp_business_management.', 'templates' => []];
                }
            }

            $r    = $this->fetchTemplates($account, $wabaId);
            $res  = $r['res'];
            $body = $r['body'];

            // WABA ID inválido → reintentar con el resuelto desde el Phone Number ID.
            if (!$res->successful()) {
                $metaMsg = $body['error']['message'] ?? '';
                $isBadWaba = str_contains($metaMsg, 'message_templates')
                    || str_contains($metaMsg, 'nonexisting field')
                    || str_contains($metaMsg, 'Unsupported get request');

                if ($isBadWaba) {
                    $resolved = $this->resolveWabaId($account);
                    if ($resolved && $resolved !== (string) $wabaId) {
                        $r    = $this->fetchTemplates($account, $resolved);
                        $res  = $r['res'];
                        $body = $r['body'];
                    }
                }
            }

            if (!$res->successful()) {
                $metaMsg = $body['error']['message'] ?? 'No se pudieron obtener las plantillas.';
                if (str_contains($metaMsg, 'message_templates') || str_contains($metaMsg, 'nonexisting field')) {
                    $metaMsg = 'El WABA ID no es válido. Ve a "WhatsApp → Cuentas → Editar" y usa "Auto-detectar" para obtener el WABA ID correcto (NO el Phone Number ID).';
                } elseif (str_contains(strtolower($metaMsg), 'permission')) {
                    $metaMsg = 'El Access Token no tiene permiso "whatsapp_business_management".';
                }
                return ['ok' => false, 'message' => $metaMsg, 'templates' => []];
            }

            $data = $res->json('data') ?? [];
            // Orden: pendientes y rechazadas primero, luego aprobadas; por nombre.
            usort($data, fn($a, $b) => strcasecmp($a['name'] ?? '', $b['name'] ?? ''));

            return ['ok' => true, 'templates' => $data];
        } catch (\Throwable $e) {
            Log::error('WA listAll templates error: ' . $e->getMessage());
            return ['ok' => false, 'message' => $e->getMessage(), 'templates' => []];
        }
    }

    /**
     * Crea una plantilla en Meta. $payload = ['name','language','category','components'].
     */
    public function create(WhatsappAccount $account, array $payload): array
    {
        if (!$account->waba_id || !$account->access_token) {
            return ['ok' => false, 'message' => 'La cuenta no tiene WABA ID o Access Token configurado.'];
        }

        try {
            $res = Http::withToken($account->access_token)
                ->acceptJson()->timeout(20)
                ->post(self::GRAPH . "/{$account->waba_id}/message_templates", $payload);

            $body = $res->json() ?? [];

            if (!$res->successful()) {
                $msg = $body['error']['error_user_msg'] ?? ($body['error']['message'] ?? 'No se pudo crear la plantilla.');
                return ['ok' => false, 'message' => $msg, 'raw' => $body];
            }

            Cache::forget("wa_templates:{$account->id}");
            return ['ok' => true, 'data' => $body];
        } catch (\Throwable $e) {
            Log::error('WA create template error: ' . $e->getMessage());
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Sube un archivo de muestra (imagen/vídeo/documento) a Meta mediante el
     * Resumable Upload API y devuelve el "header_handle" para usar en una plantilla.
     *
     * @return array ['ok'=>bool, 'handle'=>string|null, 'message'=>string|null]
     */
    public function uploadSample(WhatsappAccount $account, string $filePath, string $mime, string $fileName): array
    {
        if (empty($account->app_id)) {
            return ['ok' => false, 'handle' => null, 'message' => 'La cuenta no tiene App ID de Meta configurado (WhatsApp → Cuentas → Editar).'];
        }
        if (!is_file($filePath)) {
            return ['ok' => false, 'handle' => null, 'message' => 'No se pudo leer el archivo subido.'];
        }

        try {
            $length = filesize($filePath);

            // 1) Iniciar sesión de carga: /{app-id}/uploads
            $start = Http::withToken($account->access_token)
                ->acceptJson()->timeout(30)
                ->post(self::GRAPH . "/{$account->app_id}/uploads", [
                    'file_name'   => $fileName,
                    'file_length' => $length,
                    'file_type'   => $mime,
                ]);

            $startBody = $start->json() ?? [];
            if (!$start->successful() || empty($startBody['id'])) {
                $msg = $startBody['error']['error_user_msg'] ?? ($startBody['error']['message'] ?? 'No se pudo iniciar la carga del archivo.');
                return ['ok' => false, 'handle' => null, 'message' => $msg];
            }

            $uploadId = $startBody['id']; // "upload:XXXXXXXX"

            // 2) Subir los bytes: POST /{upload-id} con header OAuth + file_offset
            $upload = Http::withHeaders([
                    'Authorization' => "OAuth {$account->access_token}",
                    'file_offset'   => '0',
                ])
                ->timeout(120)
                ->withBody(file_get_contents($filePath), $mime)
                ->post(self::GRAPH . "/{$uploadId}");

            $uploadBody = $upload->json() ?? [];
            if (!$upload->successful() || empty($uploadBody['h'])) {
                $msg = $uploadBody['error']['error_user_msg'] ?? ($uploadBody['error']['message'] ?? 'No se pudo subir el archivo a Meta.');
                return ['ok' => false, 'handle' => null, 'message' => $msg];
            }

            return ['ok' => true, 'handle' => $uploadBody['h'], 'message' => null];
        } catch (\Throwable $e) {
            Log::error('WA uploadSample error: ' . $e->getMessage());
            return ['ok' => false, 'handle' => null, 'message' => $e->getMessage()];
        }
    }

    /** Elimina una plantilla por nombre. */
    public function delete(WhatsappAccount $account, string $name): array
    {
        if (!$account->waba_id || !$account->access_token) {
            return ['ok' => false, 'message' => 'La cuenta no tiene WABA ID o Access Token configurado.'];
        }

        try {
            $res = Http::withToken($account->access_token)
                ->acceptJson()->timeout(20)
                ->delete(self::GRAPH . "/{$account->waba_id}/message_templates", ['name' => $name]);

            $body = $res->json() ?? [];
            if (!$res->successful()) {
                return ['ok' => false, 'message' => $body['error']['message'] ?? 'No se pudo eliminar la plantilla.'];
            }

            Cache::forget("wa_templates:{$account->id}");
            return ['ok' => true];
        } catch (\Throwable $e) {
            Log::error('WA delete template error: ' . $e->getMessage());
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Envía un mensaje template al cliente.
     *
     * @param WhatsappAccount $account
     * @param string          $toPhone     número destino (E.164 sin +)
     * @param string          $name        nombre de la plantilla
     * @param string          $language    código de idioma (ej: es_PE, es, en_US)
     * @param array           $bodyParams  parámetros para variables {{1}},{{2}}…  ej: ['Juan', 'S/ 49.90']
     * @param array           $headerParams parámetros del header si aplica (texto)
     */
    public function sendTemplate(
        WhatsappAccount $account,
        string $toPhone,
        string $name,
        string $language,
        array  $bodyParams = [],
        array  $headerParams = []
    ): array {
        $components = [];

        if (!empty($headerParams)) {
            $components[] = [
                'type'       => 'header',
                'parameters' => array_map(fn($v) => ['type' => 'text', 'text' => (string) $v], $headerParams),
            ];
        }

        if (!empty($bodyParams)) {
            $components[] = [
                'type'       => 'body',
                'parameters' => array_map(fn($v) => ['type' => 'text', 'text' => (string) $v], $bodyParams),
            ];
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'to'                => $toPhone,
            'type'              => 'template',
            'template'          => [
                'name'     => $name,
                'language' => ['code' => $language],
            ],
        ];

        if (!empty($components)) {
            $payload['template']['components'] = $components;
        }

        try {
            $res = Http::withToken($account->access_token)
                ->acceptJson()
                ->timeout(20)
                ->post(self::GRAPH . "/{$account->phone_number_id}/messages", $payload);

            $body = $res->json() ?? [];

            if (!$res->successful()) {
                return [
                    'ok'      => false,
                    'message' => $body['error']['message'] ?? 'Error al enviar plantilla.',
                    'raw'     => $body,
                ];
            }

            return ['ok' => true, 'response' => $body];
        } catch (\Throwable $e) {
            Log::error('WA sendTemplate error: ' . $e->getMessage());
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }
}
