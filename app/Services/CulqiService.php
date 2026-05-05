<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CulqiService
{
    private const API_BASE = 'https://api.culqi.com/v2';

    public function isConfigured(): bool
    {
        return !empty(config('services.culqi.secret_key'))
            && !empty(config('services.culqi.public_key'));
    }

    /**
     * Crea un cargo (charge) en Culqi.
     *
     * @param string $token       token (source) generado por Culqi.js (cyXX...)
     * @param int    $amountCents monto en céntimos: 4990 = S/ 49.90
     * @param string $currency    'PEN' o 'USD'
     * @param string $email       correo del comprador
     * @param array  $metadata    datos adicionales (team_id, etc.)
     * @param string $description descripción del cobro (máx 80 chars)
     */
    public function createCharge(
        string $token,
        int    $amountCents,
        string $currency,
        string $email,
        array  $metadata = [],
        string $description = ''
    ): array {
        $payload = [
            'amount'        => $amountCents,
            'currency_code' => strtoupper($currency),
            'email'         => $email,
            'source_id'     => $token,
        ];

        if ($description) {
            $payload['description'] = mb_substr($description, 0, 80);
        }
        if (!empty($metadata)) {
            // Culqi acepta hasta 50 keys, valores ≤ 87 chars; las casteamos a string
            $payload['metadata'] = collect($metadata)
                ->take(50)
                ->map(fn($v) => mb_substr((string) $v, 0, 87))
                ->all();
        }

        try {
            $response = Http::withToken(config('services.culqi.secret_key'))
                ->acceptJson()
                ->asJson()
                ->timeout(20)
                ->post(self::API_BASE . '/charges', $payload);

            $body = $response->json() ?? [];

            if ($response->successful() && !empty($body['id'])) {
                return [
                    'ok'      => true,
                    'charge'  => $body,
                ];
            }

            // Error documentado por Culqi
            $userMsg = $body['user_message'] ?? $body['merchant_message'] ?? 'Error procesando el pago.';
            return [
                'ok'      => false,
                'message' => $userMsg,
                'raw'     => $body,
            ];
        } catch (\Throwable $e) {
            Log::error('Culqi createCharge exception: ' . $e->getMessage());
            return [
                'ok'      => false,
                'message' => 'No se pudo comunicar con Culqi. Intenta nuevamente.',
                'raw'     => ['exception' => $e->getMessage()],
            ];
        }
    }

    /**
     * Verifica una firma de webhook (HMAC SHA256).
     * Culqi envía la cabecera HTTP_CULQI_SIGNATURE con el HMAC del payload.
     */
    public function verifyWebhook(string $payload, ?string $signature): bool
    {
        $secret = config('services.culqi.webhook_secret');
        if (!$secret || !$signature) return false;

        $expected = hash_hmac('sha256', $payload, $secret);
        return hash_equals($expected, $signature);
    }
}
