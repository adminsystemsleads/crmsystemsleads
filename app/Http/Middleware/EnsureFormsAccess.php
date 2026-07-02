<?php

namespace App\Http\Middleware;

use App\Support\FormsFeature;
use Closure;
use Illuminate\Http\Request;

class EnsureFormsAccess
{
    /**
     * Bloquea el acceso al módulo de Formularios mientras está en desarrollo,
     * salvo para los usuarios con acceso anticipado (ver FormsFeature).
     */
    public function handle(Request $request, Closure $next)
    {
        abort_unless(FormsFeature::accessibleBy($request->user()), 404);

        return $next($request);
    }
}
