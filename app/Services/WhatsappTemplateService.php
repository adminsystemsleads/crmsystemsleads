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
     * Descubre los WABA IDs accesibles con un token:
     *  A) Desde los permisos granulares del token (debug_token → granular_scopes).
     *  B) Desde las WABA propias y de clientes del Business (Portfolio) ID.
     *
     * @return string[] lista de WABA IDs candidatos (sin duplicados)
     */
    public function discoverWabaIds(string $token, ?string $businessId = null): array
    {
        $ids = [];

        // A) granular_scopes del token
        try {
            $dbg = Http::withToken($token)->acceptJson()->timeout(15)
                ->get(self::GRAPH . '/debug_token', ['input_token' => $token]);
            foreach (($dbg->json('data.granular_scopes') ?? []) as $gs) {
                $scope = $gs['scope'] ?? '';
                if (in_array($scope, ['whatsapp_business_management', 'whatsapp_business_messaging'], true)) {
                    foreach (($gs['target_ids'] ?? []) as $tid) {
                        $ids[] = (string) $tid;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('WA discoverWabaIds debug_token: ' . $e->getMessage());
        }

        // B) WABA del Business ID (propias y de clientes)
        if ($businessId) {
            foreach (['owned_whatsapp_business_accounts', 'client_whatsapp_business_accounts'] as $edge) {
                try {
                    $r = Http::withToken($token)->acceptJson()->timeout(15)
                        ->get(self::GRAPH . "/{$businessId}/{$edge}", ['fields' => 'id', 'limit' => 100]);
                    foreach (($r->json('data') ?? []) as $w) {
                        if (!empty($w['id'])) $ids[] = (string) $w['id'];
                    }
                } catch (\Throwable $e) {
                    Log::warning("WA discoverWabaIds {$edge}: " . $e->getMessage());
                }
            }
        }

        return array_values(array_unique($ids));
    }

    /** Indica si un WABA contiene el Phone Number ID dado. */
    public function wabaOwnsPhone(string $token, string $wabaId, string $phoneId): bool
    {
        try {
            $r = Http::withToken($token)->acceptJson()->timeout(15)
                ->get(self::GRAPH . "/{$wabaId}/phone_numbers", ['fields' => 'id', 'limit' => 100]);
            foreach (($r->json('data') ?? []) as $p) {
                if ((string) ($p['id'] ?? '') === (string) $phoneId) return true;
            }
        } catch (\Throwable $e) {
            Log::warning('WA wabaOwnsPhone: ' . $e->getMessage());
        }
        return false;
    }

    /**
     * Resuelve el WABA ID real de una cuenta y lo persiste. Retorna el WABA ID o null.
     * Si hay varios candidatos, elige el que contiene el Phone Number ID de la cuenta.
     */
    public function resolveWabaId(WhatsappAccount $account): ?string
    {
        if (!$account->access_token) return null;

        $candidates = $this->discoverWabaIds($account->access_token, $account->business_id);
        if (empty($candidates)) return null;

        $chosen = null;
        if ($account->phone_number_id && count($candidates) > 1) {
            foreach ($candidates as $c) {
                if ($this->wabaOwnsPhone($account->access_token, $c, $account->phone_number_id)) {
                    $chosen = $c;
                    break;
                }
            }
        }
        $chosen = $chosen ?? $candidates[0];

        if ((string) $account->waba_id !== (string) $chosen) {
            $account->forceFill(['waba_id' => $chosen])->saveQuietly();
        }

        return (string) $chosen;
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

    /**
     * Descarga el archivo de muestra de una plantilla (URL) y lo re-sube al endpoint
     * de medios del número para obtener un "media id" propio que Meta sí entrega.
     * Cachea el id 20 min (mismo archivo → mismo id reutilizable).
     *
     * @return array ['ok'=>bool, 'id'=>string|null, 'message'=>string|null]
     */
    public function uploadMediaFromUrl(WhatsappAccount $account, string $url, string $format): array
    {
        if (empty($account->phone_number_id) || empty($account->access_token)) {
            return ['ok' => false, 'id' => null, 'message' => 'La cuenta no tiene Phone Number ID o Access Token.'];
        }

        $cacheKey = 'wa_media_id:' . $account->id . ':' . md5($url);
        if ($cached = Cache::get($cacheKey)) {
            return ['ok' => true, 'id' => $cached, 'message' => null];
        }

        try {
            // 1) Descargar el archivo desde la URL de muestra.
            $dl = Http::withToken($account->access_token)->timeout(60)->get($url);
            if (!$dl->successful()) {
                $dl = Http::timeout(60)->get($url); // reintento sin token
            }
            if (!$dl->successful()) {
                return ['ok' => false, 'id' => null, 'message' => 'No se pudo descargar el archivo de muestra de la plantilla.'];
            }

            $bytes = $dl->body();
            $mime  = $dl->header('Content-Type');
            if (!$mime || stripos($mime, 'text/') === 0) {
                $mime = $this->defaultMimeFor($format);
            }
            $mime = trim(explode(';', $mime)[0]);
            $ext  = $this->extForMime($mime, $format);

            // 2) Subir al endpoint de medios del número.
            $up = Http::withToken($account->access_token)
                ->attach('file', $bytes, 'header.' . $ext, ['Content-Type' => $mime])
                ->post(self::GRAPH . "/{$account->phone_number_id}/media", [
                    'messaging_product' => 'whatsapp',
                    'type'              => $mime,
                ]);

            $body = $up->json() ?? [];
            if (!$up->successful() || empty($body['id'])) {
                return ['ok' => false, 'id' => null, 'message' => $body['error']['message'] ?? 'No se pudo subir el archivo a WhatsApp.'];
            }

            Cache::put($cacheKey, $body['id'], now()->addMinutes(20));
            return ['ok' => true, 'id' => $body['id'], 'message' => null];
        } catch (\Throwable $e) {
            Log::error('WA uploadMediaFromUrl error: ' . $e->getMessage());
            return ['ok' => false, 'id' => null, 'message' => $e->getMessage()];
        }
    }

    private function defaultMimeFor(string $format): string
    {
        $m = ['IMAGE' => 'image/jpeg', 'VIDEO' => 'video/mp4', 'DOCUMENT' => 'application/pdf'];
        return $m[strtoupper($format)] ?? 'application/octet-stream';
    }

    private function extForMime(string $mime, string $format): string
    {
        $m = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'video/mp4' => 'mp4', 'video/3gpp' => '3gp', 'application/pdf' => 'pdf'];
        if (isset($m[$mime])) return $m[$mime];
        $f = ['IMAGE' => 'jpg', 'VIDEO' => 'mp4', 'DOCUMENT' => 'pdf'];
        return $f[strtoupper($format)] ?? 'bin';
    }

    /**
     * Resuelve el componente de encabezado multimedia (con media id) a partir de
     * las componentes de una plantilla. Devuelve ['format'=>..,'id'=>..] o null.
     */
    public function resolveHeaderMedia(WhatsappAccount $account, array $components): ?array
    {
        foreach ($components as $comp) {
            if (strtoupper($comp['type'] ?? '') !== 'HEADER') continue;
            $fmt = strtoupper($comp['format'] ?? '');
            if (!in_array($fmt, ['IMAGE', 'VIDEO', 'DOCUMENT'], true)) continue;

            $url = $comp['example']['header_handle'][0] ?? null;
            if (!$url) return null;

            $res = $this->uploadMediaFromUrl($account, $url, $fmt);
            if ($res['ok']) {
                return ['format' => $fmt, 'id' => $res['id']];
            }
            // Fallback: enviar por link (menos fiable).
            return ['format' => $fmt, 'link' => $url];
        }
        return null;
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
        array  $headerParams = [],
        ?array $headerMedia = null
    ): array {
        $components = [];

        // Encabezado multimedia (imagen/vídeo/documento): debe enviarse el archivo.
        if (!empty($headerMedia['format']) && (!empty($headerMedia['id']) || !empty($headerMedia['link']))) {
            $fmt = strtolower($headerMedia['format']); // image | video | document
            if (in_array($fmt, ['image', 'video', 'document'], true)) {
                $param = !empty($headerMedia['id'])
                    ? ['id' => $headerMedia['id']]
                    : ['link' => $headerMedia['link']];
                $components[] = [
                    'type'       => 'header',
                    'parameters' => [[ 'type' => $fmt, $fmt => $param ]],
                ];
            }
        } elseif (!empty($headerParams)) {
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
