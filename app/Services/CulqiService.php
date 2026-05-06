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

    private function http()
    {
        return Http::withToken(config('services.culqi.secret_key'))
            ->acceptJson()
            ->asJson()
            ->timeout(30);
    }

    private function handleResponse(\Illuminate\Http\Client\Response $response, string $okKey = 'id'): array
    {
        $body = $response->json() ?? [];
        if ($response->successful() && !empty($body[$okKey])) {
            return ['ok' => true, 'data' => $body];
        }
        return [
            'ok'      => false,
            'message' => $body['user_message'] ?? $body['merchant_message'] ?? ($body['object'] ?? 'Error en Culqi'),
            'raw'     => $body,
        ];
    }

    /* ============ Cargo único ============ */

    public function createCharge(string $token, int $amountCents, string $currency, string $email, array $metadata = [], string $description = ''): array
    {
        $payload = [
            'amount'        => $amountCents,
            'currency_code' => strtoupper($currency),
            'email'         => $email,
            'source_id'     => $token,
        ];
        if ($description) $payload['description'] = mb_substr($description, 0, 80);
        if ($metadata)    $payload['metadata']    = $this->cleanMetadata($metadata);

        try {
            return $this->wrapResponse($this->http()->post(self::API_BASE.'/charges', $payload), 'charge');
        } catch (\Throwable $e) {
            Log::error('Culqi createCharge: ' . $e->getMessage());
            return ['ok' => false, 'message' => 'No se pudo comunicar con Culqi.', 'raw' => ['exception' => $e->getMessage()]];
        }
    }

    /* ============ Customer + Card + Plan + Subscription ============ */

    public function createCustomer(string $email, string $firstName, string $lastName, string $address, string $city, string $countryCode, ?string $phone = null): array
    {
        $payload = [
            'first_name'   => $firstName,
            'last_name'    => $lastName,
            'address'      => $address ?: 'Av. Lima 123',
            'address_city' => mb_strtoupper($city ?: 'LIMA'),
            'country_code' => mb_strtoupper($countryCode ?: 'PE'),
            'email'        => $email,
            'phone_number' => $phone ?: '999999999',
        ];

        try {
            return $this->wrapResponse($this->http()->post(self::API_BASE.'/customers', $payload), 'customer');
        } catch (\Throwable $e) {
            Log::error('Culqi createCustomer: ' . $e->getMessage());
            return ['ok' => false, 'message' => 'No se pudo crear el cliente en Culqi.', 'raw' => ['exception' => $e->getMessage()]];
        }
    }

    public function createCard(string $customerId, string $tokenId): array
    {
        try {
            return $this->wrapResponse(
                $this->http()->post(self::API_BASE.'/cards', [
                    'customer_id' => $customerId,
                    'token_id'    => $tokenId,
                ]),
                'card'
            );
        } catch (\Throwable $e) {
            Log::error('Culqi createCard: ' . $e->getMessage());
            return ['ok' => false, 'message' => 'No se pudo guardar la tarjeta.', 'raw' => ['exception' => $e->getMessage()]];
        }
    }

    public function createPlan(string $shortName, string $name, int $amountCents, string $currency, string $interval, int $intervalCount = 1, int $trialDays = 0, ?string $description = null): array
    {
        $payload = [
            'name'           => $name,
            'short_name'     => $shortName,
            'description'    => $description ?: $name,
            'amount'         => $amountCents,
            'currency'       => strtoupper($currency),
            'interval'       => mb_strtolower($interval),
            'interval_count' => $intervalCount,
            'limit'          => 0,        // 0 = ilimitado
            'trial_days'     => $trialDays,
        ];

        try {
            return $this->wrapResponse($this->http()->post(self::API_BASE.'/plans', $payload), 'plan');
        } catch (\Throwable $e) {
            Log::error('Culqi createPlan: ' . $e->getMessage());
            return ['ok' => false, 'message' => 'No se pudo crear el plan.', 'raw' => ['exception' => $e->getMessage()]];
        }
    }

    public function createSubscription(string $cardId, string $planId): array
    {
        try {
            return $this->wrapResponse(
                $this->http()->post(self::API_BASE.'/subscriptions', [
                    'card_id' => $cardId,
                    'plan_id' => $planId,
                ]),
                'subscription'
            );
        } catch (\Throwable $e) {
            Log::error('Culqi createSubscription: ' . $e->getMessage());
            return ['ok' => false, 'message' => 'No se pudo crear la suscripción.', 'raw' => ['exception' => $e->getMessage()]];
        }
    }

    public function cancelSubscription(string $subscriptionId): array
    {
        try {
            $resp = $this->http()->delete(self::API_BASE.'/subscriptions/'.$subscriptionId);
            $body = $resp->json() ?? [];
            return ['ok' => $resp->successful(), 'data' => $body, 'message' => $body['user_message'] ?? null];
        } catch (\Throwable $e) {
            Log::error('Culqi cancelSubscription: ' . $e->getMessage());
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    public function getSubscription(string $subscriptionId): array
    {
        try {
            $resp = $this->http()->get(self::API_BASE.'/subscriptions/'.$subscriptionId);
            return ['ok' => $resp->successful(), 'data' => $resp->json() ?? []];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    /* ============ Webhook ============ */

    public function verifyWebhook(string $payload, ?string $signature): bool
    {
        $secret = config('services.culqi.webhook_secret');
        if (!$secret || !$signature) return false;

        $expected = hash_hmac('sha256', $payload, $secret);
        return hash_equals($expected, $signature);
    }

    /* ============ Helpers ============ */

    private function wrapResponse(\Illuminate\Http\Client\Response $response, string $entity): array
    {
        $body = $response->json() ?? [];
        if ($response->successful() && !empty($body['id'])) {
            return ['ok' => true, $entity => $body];
        }
        return [
            'ok'      => false,
            'message' => $body['user_message'] ?? $body['merchant_message'] ?? "Error en Culqi ({$entity})",
            'raw'     => $body,
        ];
    }

    private function cleanMetadata(array $metadata): array
    {
        return collect($metadata)
            ->take(50)
            ->map(fn($v) => mb_substr((string) $v, 0, 87))
            ->all();
    }
}
