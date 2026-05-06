<?php

namespace App\Console\Commands;

use App\Models\SubscriptionPlan;
use App\Services\CulqiService;
use Illuminate\Console\Command;

class SyncCulqiPlans extends Command
{
    protected $signature = 'culqi:sync-plans';
    protected $description = 'Crea en Culqi todos los planes locales que aún no tengan culqi_plan_id';

    public function handle(CulqiService $culqi): int
    {
        if (!$culqi->isConfigured()) {
            $this->error('Culqi no está configurado. Define CULQI_PUBLIC_KEY y CULQI_SECRET_KEY en .env');
            return self::FAILURE;
        }

        $plans = SubscriptionPlan::where('is_active', true)
            ->whereNull('culqi_plan_id')
            ->get();

        if ($plans->isEmpty()) {
            $this->info('Todos los planes activos ya están sincronizados con Culqi.');
            return self::SUCCESS;
        }

        foreach ($plans as $plan) {
            $this->info("Creando plan en Culqi: {$plan->name} ({$plan->slug})");
            $result = $culqi->createPlan(
                shortName:     $plan->slug,
                name:          $plan->name,
                amountCents:   $plan->amount_cents,
                currency:      $plan->currency,
                interval:      $plan->interval,
                intervalCount: $plan->interval_count,
                trialDays:     $plan->trial_days,
                description:   $plan->description
            );

            if (!$result['ok']) {
                $this->error("  ✗ {$result['message']}");
                continue;
            }

            $plan->update(['culqi_plan_id' => $result['plan']['id']]);
            $this->info("  ✓ {$result['plan']['id']}");
        }

        return self::SUCCESS;
    }
}
