<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        SubscriptionPlan::firstOrCreate(
            ['slug' => 'qipu_basic'],
            [
                'name'           => 'QipuCRM Mensual',
                'description'    => 'Acceso completo al CRM con renovación automática mensual.',
                'amount_cents'   => 4990,        // S/ 49.90
                'currency'       => 'PEN',
                'interval'       => 'meses',
                'interval_count' => 1,
                'trial_days'     => 0,
                'is_active'      => true,
                'features'       => [
                    'Pipelines y Kanban ilimitados',
                    'Contactos y productos ilimitados',
                    'WhatsApp con Asistente IA',
                    'Facturación electrónica SUNAT',
                    'Multi-usuario',
                ],
            ]
        );
    }
}
