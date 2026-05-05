<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Team;
use App\Services\CulqiService;
use App\Services\TeamLicenseManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function checkout(Team $team)
    {
        if (!Auth::user()->belongsToTeam($team)) {
            abort(403);
        }

        $config = [
            'public_key'  => config('services.culqi.public_key'),
            'amount'      => (int) config('services.culqi.plan_amount_cents', 4990),
            'currency'    => strtoupper(config('services.culqi.plan_currency', 'PEN')),
            'plan_name'   => config('services.culqi.plan_name', 'QipuCRM Mensual'),
            'configured'  => app(CulqiService::class)->isConfigured(),
        ];

        $payments = Payment::where('team_id', $team->id)
            ->latest()
            ->limit(10)
            ->get();

        return view('payments.checkout', compact('team', 'config', 'payments'));
    }

    public function process(Request $request, Team $team, CulqiService $culqi, TeamLicenseManager $licenses)
    {
        if (!Auth::user()->belongsToTeam($team)) {
            abort(403);
        }

        $data = $request->validate([
            'token'  => 'required|string|max:100',
            'email'  => 'required|email|max:255',
            'months' => 'nullable|integer|min:1|max:36',
        ]);

        $months  = (int) ($data['months'] ?? 1);
        $amount  = (int) config('services.culqi.plan_amount_cents', 4990) * $months;
        $currency= strtoupper(config('services.culqi.plan_currency', 'PEN'));

        // 1) Crear registro pendiente
        $payment = Payment::create([
            'team_id'      => $team->id,
            'user_id'      => Auth::id(),
            'provider'     => 'culqi',
            'source_id'    => $data['token'],
            'amount_cents' => $amount,
            'currency'     => $currency,
            'status'       => 'pending',
            'months'       => $months,
            'email'        => $data['email'],
            'description'  => "Licencia QipuCRM x {$months} mes(es)",
        ]);

        // 2) Crear cargo en Culqi
        $result = $culqi->createCharge(
            token:       $data['token'],
            amountCents: $amount,
            currency:    $currency,
            email:       $data['email'],
            metadata:    ['team_id' => (string) $team->id, 'payment_id' => (string) $payment->id, 'months' => (string) $months],
            description: "QipuCRM Lic. team {$team->id} x{$months}m"
        );

        if (!$result['ok']) {
            $payment->update([
                'status'        => 'failed',
                'error_message' => $result['message'] ?? 'Error desconocido',
                'response'      => $result['raw'] ?? null,
            ]);
            return back()->with('error', '❌ Pago rechazado: ' . ($result['message'] ?? ''));
        }

        // 3) Cargo aceptado: marcar pago + activar/renovar licencia
        $charge = $result['charge'];
        $payment->update([
            'status'    => 'paid',
            'charge_id' => $charge['id'] ?? null,
            'response'  => $charge,
            'paid_at'   => now(),
        ]);

        $licenseKey = 'CULQI-' . strtoupper(substr($charge['id'] ?? uniqid(), 0, 12));
        $licenses->activate($team, $licenseKey, $months);

        return redirect()->route('team.license.form', $team)
            ->with('success', "✅ Pago aceptado. Licencia activada por {$months} mes(es).");
    }

    public function webhook(Request $request, CulqiService $culqi)
    {
        $raw = $request->getContent();
        $signature = $request->header('Culqi-Signature') ?? $request->header('X-Culqi-Signature');

        if (!$culqi->verifyWebhook($raw, $signature)) {
            Log::warning('Culqi webhook con firma inválida.');
            return response()->json(['ok' => false], 400);
        }

        $payload = json_decode($raw, true) ?? [];
        Log::info('Culqi webhook OK', $payload);

        // Aquí podrías reaccionar a eventos como charge.creation.succeeded, refund.creation.succeeded, etc.
        return response()->json(['ok' => true]);
    }
}
