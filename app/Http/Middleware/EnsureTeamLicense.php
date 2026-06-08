<?php


namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\TeamLicenseManager;

class EnsureTeamLicense
{
    public function handle(Request $request, Closure $next)
    {
        // Permitir login, logout, páginas de activación y el panel del Super Admin
        if ($request->routeIs([
            'team.license.form',     // coincide también si viene con {team}
            'team.license.activate',
            'admin.license-codes.*', // generación de códigos (Super Admin)
            'soporte',               // soporte accesible aunque la licencia esté vencida
            'login', 'logout', 'password.*'
        ])) {
            return $next($request);
        }

        $user = Auth::user();

        // El Super Administrador nunca queda bloqueado por licencia de team
        if ($user && $user->isSuperAdmin()) {
            return $next($request);
        }

        // 1) Resuelve el team desde la ruta o desde el usuario
        //    (si tu ruta es /teams/{team}/..., route('team') será el modelo Team por binding)
        $routeTeam = $request->route('team');
        $team = $routeTeam ?: ($user?->currentTeam);

        if (!$user || !$team) {
            // No hay team resuelto -> manda a un lugar seguro
            return redirect()->route('teams.index')
                ->withErrors('No hay equipo activo.');
        }

        // 2) Revisa la licencia del team
        $license = $team->license; // relación Team::license()

        // Bloqueo manual (is_active=false) o sin licencia -> vista bloqueada.
        // Tiene prioridad: un bloqueo manual NO debe disparar prórroga automática.
        if (!$license || !$license->is_active) {
            return response()->view('licencia.bloqueada', ['team' => $team], 403);
        }

        // Si venció una LICENCIA pagada (meses), se otorga automáticamente un periodo
        // de prórroga de 7 días (una sola vez) para que el cliente exporte su data.
        // Una prueba vencida NO genera prórroga.
        if ($license->is_expired) {
            $wasPaidLicense = $license->grant_type === 'license'
                || ($license->grant_type === null && $license->active_until !== null);

            if ($wasPaidLicense) {
                app(TeamLicenseManager::class)->grantProrroga($team, 7);
                return $next($request);
            }

            // Prueba o prórroga vencida -> cuenta bloqueada.
            return response()->view('licencia.bloqueada', ['team' => $team], 403);
        }

        return $next($request);
    }
}
