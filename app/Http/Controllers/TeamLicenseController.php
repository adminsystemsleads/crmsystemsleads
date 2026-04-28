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
            'months'      => 'nullable|integer|min:1|max:36'
        ]);

        $months = (int)($data['months'] ?? 1);

        $res = $svc->activate($team, $data['license_key'], $months);

        if (!$res['ok']) {
            return back()->with('error', 'No se pudo activar la licencia.');
        }

        return redirect()->route('dashboard')->with('success', 'Licencia activada/renovada correctamente.');
    }
   public function show(Team $team)
{

    if (!Auth::user()->belongsToTeam($team)) {
        abort(403);
    }
    // (opcional) seguridad extra si manejas equipos
    // abort_unless(Auth::user()->belongsToTeam($team), 403);

    // Crea licencia si no existe (30 días de prueba)
    $license = $team->license()->firstOrCreate([], [
        'starts_at'    => now(),
        'active_until' => now()->addDays(30),
        'is_active'    => true,
    ]);

    // Cálculo simple de estado
    $isTrial = $license->starts_at && $license->active_until
                ? $license->starts_at->diffInDays($license->active_until) <= 31
                : false;

    $status = [
        'valid'  => $license->is_active && optional($license->active_until)->isFuture(),
        'reason' => $isTrial ? 'trial' : 'paid',
    ];

    return view('teams.license', [
        'team'    => $team,
        'license' => $license,
        'status'  => $status,
    ]);
}
}
