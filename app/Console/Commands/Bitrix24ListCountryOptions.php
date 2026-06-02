<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class Bitrix24ListCountryOptions extends Command
{
    protected $signature = 'bitrix24:list-country-options {entity=deal : deal o contact}';

    protected $description = 'Lista las opciones (IDs) del campo País en Bitrix24 para configurar el mapeo';

    public function handle(): int
    {
        $webhook = (string) config('bitrix24.webhook_url');
        if (! $webhook) {
            $this->error('BITRIX24_WEBHOOK_URL no está configurado en .env');
            return self::FAILURE;
        }
        $webhook = rtrim($webhook, '/') . '/';

        $entity = strtolower((string) $this->argument('entity'));
        if (! in_array($entity, ['deal', 'contact'], true)) {
            $this->error('Entidad inválida. Usa "deal" o "contact".');
            return self::FAILURE;
        }

        $field = $entity === 'contact'
            ? config('bitrix24.contact_country_field')
            : config('bitrix24.deal_country_field');

        $endpoint = $entity === 'contact'
            ? 'crm.contact.fields.json'
            : 'crm.deal.fields.json';

        $this->info("Consultando {$endpoint} para campo {$field}...");

        $response = Http::timeout(20)->get($webhook . $endpoint);

        if (! $response->successful()) {
            $this->error('Bitrix24 devolvió HTTP ' . $response->status());
            $this->line($response->body());
            return self::FAILURE;
        }

        $fields = $response->json('result') ?? [];

        if (! isset($fields[$field])) {
            $this->error("Campo {$field} no encontrado en la respuesta de Bitrix24.");
            $this->line('Campos UF_CRM_ disponibles:');
            foreach (array_keys($fields) as $k) {
                if (str_starts_with($k, 'UF_CRM_')) $this->line(' - ' . $k);
            }
            return self::FAILURE;
        }

        $items = $fields[$field]['items'] ?? [];

        if (empty($items)) {
            $this->warn("El campo {$field} no tiene opciones (no es enumeración).");
            $this->line('Tipo: ' . ($fields[$field]['type'] ?? 'desconocido'));
            return self::SUCCESS;
        }

        $this->info("Opciones encontradas en {$field} ({$entity}):");
        $this->table(
            ['ID', 'Valor'],
            collect($items)->map(fn ($i) => [$i['ID'] ?? '?', $i['VALUE'] ?? '?'])->all()
        );

        $this->newLine();
        $this->info('Copia los IDs a tu .env. Ejemplo:');
        $this->line('  BITRIX24_COUNTRY_PE=' . ($items[0]['ID'] ?? '???') . '  # ' . ($items[0]['VALUE'] ?? '?'));

        return self::SUCCESS;
    }
}
