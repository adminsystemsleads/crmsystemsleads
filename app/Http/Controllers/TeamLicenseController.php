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
            return back()->with('error', $res['error'] ?? 'No se pudo activar la licencia.');
        }

        $message = ($res['mode'] ?? 'license') === 'trial'
            ? 'Modo de Prueba activo.'
            : 'Licencia activada.';

        return redirect()->route('dashboard')->with('success', $message);
    }
   public function show(Team $team, TeamLicenseManager $svc)
{

    if (!Auth::user()->belongsToTeam($team)) {
        abort(403);
    }

    // No crea ninguna licencia automáticamente: el cliente la activa con un código.
    $license = $team->license; // puede ser null si nunca se activó

    $status = $svc->status($team, true);

    return view('teams.license', [
        'team'    => $team,
        'license' => $license,
        'status'  => $status,
    ]);
}
}
