<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\SubscriptionPlan;
use App\Models\Team;
use App\Models\TeamSubscription;
use App\Services\CulqiService;
use App\Services\TeamLicenseManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /* ============ CHECKOUT ============ */

    public function checkout(Team $team)
    {
        if (!Auth::user()->belongsToTeam($team)) abort(403);

        $plans = SubscriptionPlan::where('is_active', true)->orderBy('amount_cents')->get();
        $subscription = TeamSubscription::where('team_id', $team->id)
            ->whereIn('status', ['active', 'past_due', 'pending'])
            ->latest()
            ->first();

        $payments = Payment::where('team_id', $team->id)->latest()->limit(10)->get();
        $configured = app(CulqiService::class)->isConfigured();
        $publicKey  = config('services.culqi.public_key');

        return view('payments.checkout', compact('team', 'plans', 'subscription', 'payments', 'configured', 'publicKey'));
    }

    /* ============ SUSCRIBIR ============ */

    public function subscribe(Request $request, Team $team, CulqiService $culqi, TeamLicenseManager $licenses)
    {
        if (!Auth::user()->belongsToTeam($team)) abort(403);

        $data = $request->validate([
            'plan_id'       => 'required|exists:subscription_plans,id',
            'token'         => 'required|string|max:100',
            'email'         => 'required|email|max:255',
            'first_name'    => 'required|string|max:100',
            'last_name'     => 'required|string|max:100',
            'phone'         => 'nullable|string|max:30',
            'address'       => 'nullable|string|max:200',
            'city'          => 'nullable|string|max:100',
            'country_code'  => 'nullable|string|size:2',
        ]);

        $plan = SubscriptionPlan::findOrFail($data['plan_id']);
        if (!$plan->is_active) {
            return back()->with('error', 'Este plan ya no está disponible.');
        }

        // Si ya hay suscripción activa, no crear otra
        $existing = TeamSubscription::where('team_id', $team->id)
            ->whereIn('status', ['active', 'past_due'])
            ->first();
        if ($existing) {
            return back()->with('error', 'Ya tienes una suscripción activa. Cancélala antes de crear otra.');
        }

        // 0) Asegurar que el plan exista en Culqi
        if (!$plan->culqi_plan_id) {
            $r = $culqi->createPlan(
                shortName:     $plan->slug,
                name:          $plan->name,
                amountCents:   $plan->amount_cents,
                currency:      $plan->currency,
                interval:      $plan->interval,
                intervalCount: $plan->interval_count,
                trialDays:     $plan->trial_days,
                description:   $plan->description
            );
            if (!$r['ok']) {
                return back()->with('error', '❌ Error al registrar el plan en Culqi: ' . $r['message']);
            }
            $plan->update(['culqi_plan_id' => $r['plan']['id']]);
        }

        // 1) Crear Customer en Culqi
        $cust = $culqi->createCustomer(
            email:       $data['email'],
            firstName:   $data['first_name'],
            lastName:    $data['last_name'],
            address:     $data['address'] ?? 'Av. Lima 123',
            city:        $data['city'] ?? 'LIMA',
            countryCode: $data['country_code'] ?? 'PE',
            phone:       $data['phone'] ?? null
        );
        if (!$cust['ok']) {
            return back()->with('error', '❌ ' . $cust['message']);
        }

        // 2) Guardar tarjeta (vincular token al customer)
        $card = $culqi->createCard($cust['customer']['id'], $data['token']);
        if (!$card['ok']) {
            return back()->with('error', '❌ Tarjeta inválida: ' . $card['message']);
        }

        // 3) Crear Subscription en Culqi
        $sub = $culqi->createSubscription($card['card']['id'], $plan->culqi_plan_id);
        if (!$sub['ok']) {
            return back()->with('error', '❌ Suscripción rechazada: ' . $sub['message']);
        }

        $cardSource = $card['card']['source'] ?? [];
        $cardBrand  = strtoupper($cardSource['iin']['card_brand'] ?? '');
        $cardLast4  = substr($cardSource['last_four'] ?? '', -4);

        // 4) Guardar registro local
        DB::transaction(function () use ($team, $data, $plan, $cust, $card, $sub, $cardBrand, $cardLast4, $licenses) {
            $teamSub = TeamSubscription::create([
                'team_id'              => $team->id,
                'user_id'              => Auth::id(),
                'subscription_plan_id' => $plan->id,
                'culqi_customer_id'    => $cust['customer']['id'],
                'culqi_card_id'        => $card['card']['id'],
                'culqi_subscription_id'=> $sub['subscription']['id'],
                'card_brand'           => $cardBrand ?: null,
                'card_last4'           => $cardLast4 ?: null,
                'status'               => 'active',
                'current_period_start' => now(),
                'current_period_end'   => now()->addMonths($plan->interval_count),
                'meta'                 => $sub['subscription'],
            ]);

            // Registrar el primer pago como cobro inicial
            Payment::create([
                'team_id'             => $team->id,
                'user_id'             => Auth::id(),
                'team_subscription_id'=> $teamSub->id,
                'provider'            => 'culqi',
                'source_id'           => $data['token'],
                'amount_cents'        => $plan->amount_cents,
                'currency'            => $plan->currency,
                'status'              => 'paid',
                'event_type'          => 'subscription.creation.succeeded',
                'months'              => $plan->interval_count,
                'email'               => $data['email'],
                'description'         => 'Suscripción ' . $plan->name . ' (inicial)',
                'response'            => $sub['subscription'],
                'paid_at'             => now(),
            ]);

            // Activar licencia
            $licenses->activate($team, 'CULQI-SUB-' . substr($sub['subscription']['id'], 0, 12), $plan->interval_count);
        });

        return redirect()->route('payments.checkout', $team)
            ->with('success', '✅ Suscripción activada. Tu tarjeta se cobrará automáticamente cada ' . $plan->interval_label . '.');
    }

    /* ============ CANCELAR ============ */

    public function cancel(Team $team, TeamSubscription $subscription, CulqiService $culqi)
    {
        if (!Auth::user()->belongsToTeam($team)) abort(403);
        abort_unless($subscription->team_id === $team->id, 404);

        if ($subscription->culqi_subscription_id) {
            $r = $culqi->cancelSubscription($subscription->culqi_subscription_id);
            if (!$r['ok']) {
                return back()->with('error', 'No se pudo cancelar en Culqi: ' . ($r['message'] ?? ''));
            }
        }

        $subscription->update([
            'status'      => 'canceled',
            'canceled_at' => now(),
        ]);

        return back()->with('success', 'Suscripción cancelada. Tu licencia sigue activa hasta ' . optional($subscription->current_period_end)->format('d/m/Y') . '.');
    }

    /* ============ WEBHOOK ============ */

    public function webhook(Request $request, CulqiService $culqi, TeamLicenseManager $licenses)
    {
        $raw       = $request->getContent();
        $signature = $request->header('Culqi-Signature') ?? $request->header('X-Culqi-Signature');

        // Si tienes secret configurado, verificamos. Si no, se acepta (modo dev).
        if (config('services.culqi.webhook_secret')) {
            if (!$culqi->verifyWebhook($raw, $signature)) {
                Log::warning('Culqi webhook con firma inválida.');
                return response()->json(['ok' => false], 400);
            }
        }

        $payload = json_decode($raw, true) ?? [];
        $type    = $payload['type'] ?? $payload['event'] ?? '';
        $object  = $payload['data'] ?? $payload['object'] ?? [];

        Log::info("Culqi webhook: {$type}", $payload);

        // charge.creation.succeeded — renovación exitosa
        if (str_contains($type, 'charge') && str_contains($type, 'succeeded')) {
            $this->handleChargeSucceeded($object, $licenses);
        }

        // charge.creation.failed — pago rechazado
        if (str_contains($type, 'charge') && str_contains($type, 'failed')) {
            $this->handleChargeFailed($object);
        }

        // subscription.deletion / cancellation
        if (str_contains($type, 'subscription') && (str_contains($type, 'deletion') || str_contains($type, 'cancel'))) {
            $subId = $object['id'] ?? null;
            if ($subId) {
                TeamSubscription::where('culqi_subscription_id', $subId)->update([
                    'status'      => 'canceled',
                    'canceled_at' => now(),
                ]);
            }
        }

        return response()->json(['ok' => true]);
    }

    private function handleChargeSucceeded(array $charge, TeamLicenseManager $licenses): void
    {
        $subId = $charge['source']['subscription_id']
              ?? $charge['metadata']['subscription_id']
              ?? null;

        $sub = null;
        if ($subId) {
            $sub = TeamSubscription::where('culqi_subscription_id', $subId)->first();
        }
        // Fallback: por customer
        if (!$sub && !empty($charge['customer_id'])) {
            $sub = TeamSubscription::where('culqi_customer_id', $charge['customer_id'])
                ->whereIn('status', ['active', 'past_due'])
                ->latest()
                ->first();
        }

        Payment::create([
            'team_id'             => $sub?->team_id ?? 0,
            'team_subscription_id'=> $sub?->id,
            'provider'            => 'culqi',
            'charge_id'           => $charge['id'] ?? null,
            'amount_cents'        => $charge['amount'] ?? 0,
            'currency'            => $charge['currency_code'] ?? 'PEN',
            'status'              => 'paid',
            'event_type'          => 'charge.creation.succeeded',
            'email'               => $charge['email'] ?? null,
            'description'         => 'Renovación automática',
            'response'            => $charge,
            'paid_at'             => now(),
        ]);

        if ($sub) {
            $months = $sub->plan?->interval_count ?? 1;
            $sub->update([
                'status'               => 'active',
                'current_period_start' => now(),
                'current_period_end'   => now()->addMonths($months),
            ]);
            $licenses->activate($sub->team, 'CULQI-SUB-' . substr($sub->culqi_subscription_id, 0, 12), $months);
        }
    }

    private function handleChargeFailed(array $charge): void
    {
        $subId = $charge['source']['subscription_id']
              ?? $charge['metadata']['subscription_id']
              ?? null;

        $sub = null;
        if ($subId) {
            $sub = TeamSubscription::where('culqi_subscription_id', $subId)->first();
        } elseif (!empty($charge['customer_id'])) {
            $sub = TeamSubscription::where('culqi_customer_id', $charge['customer_id'])
                ->whereIn('status', ['active', 'past_due'])
                ->latest()
                ->first();
        }

        Payment::create([
            'team_id'             => $sub?->team_id ?? 0,
            'team_subscription_id'=> $sub?->id,
            'provider'            => 'culqi',
            'charge_id'           => $charge['id'] ?? null,
            'amount_cents'        => $charge['amount'] ?? 0,
            'currency'            => $charge['currency_code'] ?? 'PEN',
            'status'              => 'failed',
            'event_type'          => 'charge.creation.failed',
            'email'               => $charge['email'] ?? null,
            'description'         => 'Renovación rechazada',
            'error_message'       => $charge['outcome']['user_message'] ?? null,
            'response'            => $charge,
        ]);

        $sub?->update(['status' => 'past_due']);
    }
}
