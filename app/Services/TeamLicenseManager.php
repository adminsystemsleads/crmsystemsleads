<?php

// app/Services/TeamLicenseManager.php
namespace App\Services;

use App\Models\Team;
use App\Models\TeamLicense;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;

class TeamLicenseManager
{
    protected function cacheKey(Team $team): string {
        return "team_license_status:{$team->id}";
    }

    public function status(Team $team, bool $forceRefresh = false): array
    {
        return Cache::remember($this->cacheKey($team), $forceRefresh ? 0 : now()->addMinutes(5), function () use ($team) {
            /** @var TeamLicense|null $lic */
            $lic = TeamLicense::where('team_id',$team->id)->where('is_active',true)->first();

            if (!$lic) {
                return ['valid'=>false, 'reason'=>'no_license_record', 'license'=>null];
            }

            if ($lic->is_expired) {
                return ['valid'=>false, 'reason'=>'expired', 'license'=>$lic];
            }

            return ['valid'=>true, 'reason'=> $lic->in_trial ? 'trial' : 'paid', 'license'=>$lic];
        });
    }

    /**
     * Activar / renovar una licencia (por ahora sin pasarela; tú defines duración).
     * $months = meses a sumar desde hoy (ej: 1, 12, etc.)
     */
    public function activate(Team $team, string $licenseKey, int $months = 1): array
    {
        $lic = TeamLicense::firstOrCreate(['team_id'=>$team->id], [
            'trial_starts_at'=>now(),
            'trial_ends_at'=>now()->addDays(30),
            'is_active'=>true,
        ]);

        $lic->license_key = $licenseKey;
        $lic->active_from = now();
        $lic->active_until = now()->addMonths($months);
        $lic->is_active = true;
        $lic->save();

        Cache::forget($this->cacheKey($team));

        return ['ok'=>true, 'license'=>$lic];
    }
}
