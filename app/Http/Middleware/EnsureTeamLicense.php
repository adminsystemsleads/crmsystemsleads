<?php


namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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

        // Helper para redirigir **siempre con el parámetro {team}**
        $goToLicense = function (string $msg) use ($team) {
            return redirect()->route('team.license.form', ['team' => $team->id])
                ->withErrors($msg);
        };

        if (!$license || !$license->is_active) {
            return $goToLicense('Licencia inactiva.');
        }

        // Vigente si está dentro del periodo de prueba (semanas) o de la licencia (meses).
        // is_expired = no está en trial NI tiene licencia pagada vigente.
        if ($license->is_expired) {
            return $goToLicense('Tu licencia ha expirado.');
        }

        return $next($request);
    }
}
