<?php

// app/Http/Middleware/SetTeamLocaleFromCache.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class SetTeamLocaleFromCache
{
    public function handle(Request $request, Closure $next)
    {
        $locale = 'en';

        // 1) Si la ruta tiene {team}, úsalo; si no, currentTeam
        $teamParam = $request->route('team');
        $teamId = is_object($teamParam) ? ($teamParam->id ?? null) : ($teamParam ?? null);
        if (!$teamId) {
            $teamId = Auth::user()?->currentTeam?->id;
        }

        if ($teamId) {
            $locale = Cache::get("team:{$teamId}:locale", 'en');
        }

        App::setLocale($locale);

        return $next($request);
    }
}
