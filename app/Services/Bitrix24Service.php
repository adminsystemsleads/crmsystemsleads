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
            // 1) Buscar contacto existente por teléfono o email (anti-duplicados)
            $fullPhone = $this->fullPhone($user);
            $existingId = $this->findExistingContact($fullPhone, $user->email);

            if ($existingId !== null) {
                $contactId = $existingId;
                Log::info('Bitrix24: contacto existente reutilizado', [
                    'user_id'    => $user->id,
                    'contact_id' => $contactId,
                    'matched_by' => 'phone_or_email',
                ]);
            } else {
                $contactId = $this->createContact($user);
            }

            // 2) Crear la negociación vinculada (siempre se crea, aunque el contacto sea viejo)
            $dealId = $this->createDeal($user, $contactId);

            Log::info('Bitrix24: sincronización completada', [
                'user_id'         => $user->id,
                'contact_id'      => $contactId,
                'contact_reused'  => $existingId !== null,
                'deal_id'         => $dealId,
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
     *  CONTACTO — búsqueda y creación
     * ============================================================ */

    /**
     * Busca un contacto existente por teléfono o email usando
     * crm.duplicate.findbycomm. Retorna el ID del primero encontrado
     * o null si no hay match.
     *
     * Prioridad: primero phone (más confiable), luego email.
     * Si el API falla por algún motivo, devuelve null (fallback = crear nuevo).
     */
    protected function findExistingContact(?string $phone, ?string $email): ?int
    {
        // 1) Buscar por teléfono
        if ($phone) {
            $id = $this->findContactByComm('PHONE', $phone);
            if ($id !== null) return $id;
        }

        // 2) Si no se encontró, buscar por email
        if ($email) {
            $id = $this->findContactByComm('EMAIL', $email);
            if ($id !== null) return $id;
        }

        return null;
    }

    /**
     * Llamada a crm.duplicate.findbycomm para un tipo (PHONE o EMAIL) y un valor.
     * Devuelve el primer CONTACT ID encontrado o null.
     */
    protected function findContactByComm(string $type, string $value): ?int
    {
        try {
            $response = Http::asJson()
                ->timeout(15)
                ->post($this->webhookUrl . 'crm.duplicate.findbycomm.json', [
                    'type'        => $type,
                    'values'      => [$value],
                    'entity_type' => 'CONTACT',
                ]);

            if (! $response->successful()) {
                Log::warning('Bitrix24: findbycomm devolvió HTTP no exitoso', [
                    'type'   => $type,
                    'value'  => $value,
                    'status' => $response->status(),
                ]);
                return null;
            }

            $contacts = $response->json('result.CONTACT') ?? [];
            if (empty($contacts)) return null;

            // Bitrix24 puede devolver múltiples — tomamos el primero (más antiguo)
            return (int) $contacts[0];
        } catch (\Throwable $e) {
            Log::warning('Bitrix24: error buscando duplicados, se creará contacto nuevo', [
                'type'  => $type,
                'value' => $value,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

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
