<?php

// app/Http/Controllers/TeamLicenseController.php
namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\TeamLicense;
use App\Services\TeamLicenseManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class TeamLicenseController extends Controller
{
    public function form(Request $request, Team $team, TeamLicenseManager $svc)
    {
        // Permite ver/gestionar solo a miembros del team (idealmente admin del team)
        Gate::authorize('view', $team);

        $status = $svc->status($team, true);

        return view('teams.license', [
            'team'   => $team,
            'status' => $status,
        ]);
    }

    public function activate(Request $request, Team $team, TeamLicenseManager $svc)
    {

        if (!Auth::user()->belongsToTeam($team)) {
            abort(403);
        }

        Gate::authorize('update', $team);

        $data = $request->validate([
            'license_key' => 'required|string',
        ], [], [
            'license_key' => 'código de licencia',
        ]);

        $res = $svc->redeemCode($team, $data['license_key']);

        if (!$res['ok']) {
            $error = $res['error'] ?? 'No se pudo activar la licencia.';

            return back()
                ->with('error', $error)
                ->with('flash', ['banner' => $error, 'bannerStyle' => 'danger']);
        }

        $message = match ($res['mode'] ?? 'license') {
            'trial'    => 'Código canjeado exitosamente — Modo de Prueba activo.',
            'prorroga' => 'Código canjeado exitosamente — Prórroga activada.',
            default    => 'Código canjeado exitosamente — Licencia activa.',
        };

        return redirect()->route('dashboard')
            ->with('success', $message)
            ->with('flash', ['banner' => $message, 'bannerStyle' => 'success']);
    }
   public function show(Team $team, TeamLicenseManager $svc)
{

    if (!Auth::user()->belongsToTeam($team)) {
        abort(403);
    }

    // No crea ninguna licencia automáticamente: el cliente la activa con un código.
    $license = $team->license; // puede ser null si nunca se activó

    $status = $svc->status($team, true);

    // Historial de códigos canjeados por esta cuenta (reporte)
    $redeemedCodes = \App\Models\LicenseCode::with('redeemedTeam')
        ->where('redeemed_by_team_id', $team->id)
        ->whereNotNull('redeemed_at')
        ->orderByDesc('redeemed_at')
        ->get();

    return view('teams.license', [
        'team'          => $team,
        'license'       => $license,
        'status'        => $status,
        'redeemedCodes' => $redeemedCodes,
    ]);
}
}
