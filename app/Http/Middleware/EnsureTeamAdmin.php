<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Permite acceso solo a usuarios con rol "admin" en el team actual
 * (Jetstream hasTeamRole). El owner del team siempre es admin implícito.
 *
 * Devuelve 403 si el usuario no cumple. Si quieres un redirect más amigable
 * (ej. al dashboard con un flash), cambia el abort por un redirect.
 */
class EnsureTeamAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        $team = $user?->currentTeam;

        if (! $user || ! $team) {
            abort(403, 'No hay equipo activo.');
        }

        // Admin si es dueño/admin de Jetstream o tiene el rol de CRM "Administrador".
        // (isCrmAdminFor cubre dueño, admin de Jetstream y rol CRM con permisos de admin.)
        if (! $user->isCrmAdminFor($team)) {
            abort(403, 'Solo los administradores del equipo pueden acceder a esta sección.');
        }

        return $next($request);
    }
}
