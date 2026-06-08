<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Permite acceso solo al Super Administrador de la plataforma
 * (App\Models\User::isSuperAdmin()). Devuelve 403 en caso contrario.
 */
class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (! $user || ! $user->isSuperAdmin()) {
            abort(403, 'Acceso exclusivo para el Super Administrador.');
        }

        return $next($request);
    }
}
