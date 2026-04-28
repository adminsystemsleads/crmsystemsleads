<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\App;
use Carbon\Carbon;

class SetGlobalLocale
{
    public function handle(Request $request, Closure $next)
    {
        // lee de cache o usa 'en' por defecto
        $locale = Cache::get('app:locale', 'en');

        // si alguien pasó ?lang=es, permite override rápido (opcional)
        if ($request->has('lang')) {
            $candidate = (string) $request->query('lang');
            if (in_array($candidate, config('app.supported_locales', ['en','es']))) {
                $locale = $candidate;
                Cache::forever('app:locale', $locale);
            }
        }

        App::setLocale($locale);
        Carbon::setLocale($locale);

        // Opcional para traducciones de validación con Symfony
        if (function_exists('locale_set_default')) {
            @locale_set_default($locale);
        }

        return $next($request);
    }
}
