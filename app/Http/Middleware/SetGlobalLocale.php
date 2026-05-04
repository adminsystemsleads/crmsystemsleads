<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Carbon\Carbon;

class SetGlobalLocale
{
    public function handle(Request $request, Closure $next)
    {
        $supported = config('app.supported_locales', ['es', 'en', 'pt']);
        $default   = config('app.locale', 'es');

        // Override por query string (?lang=es)
        if ($request->has('lang')) {
            $candidate = (string) $request->query('lang');
            if (in_array($candidate, $supported, true)) {
                session(['locale' => $candidate]);
            }
        }

        // Idioma desde la sesión (por usuario/navegador, no global)
        $locale = session('locale', $default);
        if (!in_array($locale, $supported, true)) {
            $locale = $default;
        }

        App::setLocale($locale);
        Carbon::setLocale($locale);

        if (function_exists('locale_set_default')) {
            @locale_set_default($locale);
        }

        return $next($request);
    }
}
