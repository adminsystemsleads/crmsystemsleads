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
                    return [
                        'ok'        => false,
                        'message'   => $body['error']['message'] ?? 'No se pudieron obtener las plantillas.',
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
