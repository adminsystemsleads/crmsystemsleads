<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Bitrix24Service
{
    protected string $webhookUrl;

    /** @var array<string, mixed> */
    protected array $config;

    public function __construct()
    {
        $this->config = (array) config('bitrix24');
        $url = (string) ($this->config['webhook_url'] ?? '');
        $this->webhookUrl = $url ? rtrim($url, '/') . '/' : '';
    }

    /**
     * Crea contacto + negociación en Bitrix24 a partir de un usuario registrado.
     *
     * @return array{contact_id: ?int, deal_id: ?int, error: ?string}
     */
    public function sendNewRegistration(User $user): array
    {
        if ($this->webhookUrl === '') {
            Log::warning('Bitrix24: webhook URL no configurado, skipping sync', ['user_id' => $user->id]);
            return ['contact_id' => null, 'deal_id' => null, 'error' => 'BITRIX24_WEBHOOK_URL no configurado'];
        }

        try {
            $contactId = $this->createContact($user);
            $dealId    = $this->createDeal($user, $contactId);

            Log::info('Bitrix24: contacto y negociación creados', [
                'user_id'    => $user->id,
                'contact_id' => $contactId,
                'deal_id'    => $dealId,
            ]);

            return ['contact_id' => $contactId, 'deal_id' => $dealId, 'error' => null];
        } catch (\Throwable $e) {
            Log::error('Bitrix24: error en sincronización', [
                'user_id' => $user->id,
                'email'   => $user->email,
                'error'   => $e->getMessage(),
            ]);
            return ['contact_id' => null, 'deal_id' => null, 'error' => $e->getMessage()];
        }
    }

    /* ============================================================
     *  CONTACTO
     * ============================================================ */

    protected function createContact(User $user): int
    {
        $fullPhone       = $this->fullPhone($user);
        $countryOptionId = $this->countryOptionFor('contact', $user->country_code);

        $fields = [
            'NAME'      => $user->name,
            'EMAIL'     => [['VALUE' => $user->email, 'VALUE_TYPE' => 'WORK']],
            'PHONE'     => [['VALUE' => $fullPhone, 'VALUE_TYPE' => 'MOBILE']],
            'SOURCE_ID' => $this->config['source_id'],
            'OPENED'    => 'Y',
        ];

        if ($countryOptionId !== null && $countryOptionId !== '') {
            $fields[$this->config['contact_country_field']] = $countryOptionId;
        }

        $response = Http::asJson()
            ->timeout(20)
            ->post($this->webhookUrl . 'crm.contact.add.json', ['fields' => $fields]);

        $body = $response->json();

        if (! $response->successful() || ! isset($body['result'])) {
            throw new \RuntimeException(
                'Bitrix24 crm.contact.add falló: ' . substr((string) $response->body(), 0, 500)
            );
        }

        return (int) $body['result'];
    }

    /* ============================================================
     *  NEGOCIACIÓN
     * ============================================================ */

    protected function createDeal(User $user, int $contactId): int
    {
        $countryOptionId = $this->countryOptionFor('deal', $user->country_code);

        $fields = [
            'TITLE'          => 'Registro Web: ' . $user->name,
            'CATEGORY_ID'    => $this->config['category_id'],
            'STAGE_ID'       => $this->config['stage_id'],
            'CONTACT_ID'     => $contactId,
            'SOURCE_ID'      => $this->config['source_id'],
            'ASSIGNED_BY_ID' => $this->config['assigned_user_id'],
            'OPENED'         => 'Y',
        ];

        if ($countryOptionId !== null && $countryOptionId !== '') {
            $fields[$this->config['deal_country_field']] = $countryOptionId;
        }

        $response = Http::asJson()
            ->timeout(20)
            ->post($this->webhookUrl . 'crm.deal.add.json', ['fields' => $fields]);

        $body = $response->json();

        if (! $response->successful() || ! isset($body['result'])) {
            throw new \RuntimeException(
                'Bitrix24 crm.deal.add falló: ' . substr((string) $response->body(), 0, 500)
            );
        }

        return (int) $body['result'];
    }

    /* ============================================================
     *  HELPERS
     * ============================================================ */

    /**
     * Construye el teléfono completo en formato internacional: +51987654321
     */
    protected function fullPhone(User $user): string
    {
        $code  = (string) ($user->country_code ?? '');
        $phone = preg_replace('/[\s\-]+/', '', (string) ($user->phone ?? ''));

        if ($phone === '') {
            return $code; // solo prefijo si no hay número (raro pero defensivo)
        }
        if (str_starts_with($phone, '+')) {
            return $phone; // ya viene completo
        }
        // Si el número ya empieza con los dígitos del prefijo, no duplicar
        $digitsOnlyCode = ltrim($code, '+');
        if ($digitsOnlyCode !== '' && str_starts_with($phone, $digitsOnlyCode)) {
            return '+' . $phone;
        }
        return $code . $phone;
    }

    /**
     * Devuelve el ID de opción de Bitrix24 para el prefijo de país dado,
     * según la entidad ("deal" o "contact"). Cada entidad tiene su propio mapeo
     * porque Bitrix24 asigna IDs distintos al mismo país en cada campo custom.
     */
    protected function countryOptionFor(string $entity, ?string $countryCode): ?string
    {
        if (! $countryCode) return null;
        $map = $entity === 'contact'
            ? ($this->config['contact_country_options'] ?? [])
            : ($this->config['deal_country_options'] ?? []);
        $value = $map[$countryCode] ?? null;
        return $value !== null && $value !== '' ? (string) $value : null;
    }
}
