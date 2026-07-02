<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Permite el acceso solo si el usuario tiene el permiso de CRM indicado en su
 * rol (los dueños/administradores lo tienen siempre). Uso en rutas:
 *   ->middleware('crm.can:admin.manage_modules')
 *
 * Si no tiene acceso, NO muestra un 403: redirige a la página anterior (o al
 * panel) con una notificación amigable.
 */
class EnsureCrmPermission
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        $user = $request->user();
        $team = $user?->currentTeam;

        if ($user && $team && $user->hasCrmPermission($permission, $team)) {
            return $next($request);
        }

        $message = __('No tienes acceso a esta sección. Solicita permisos al administrador.');

        // Para peticiones AJAX/JSON devolvemos un 403 con el mensaje (sin página de error).
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 403);
        }

        return redirect()
            ->back(fallback: route('dashboard'))
            ->with('flash.banner', $message)
            ->with('flash.bannerStyle', 'danger');
    }
}
