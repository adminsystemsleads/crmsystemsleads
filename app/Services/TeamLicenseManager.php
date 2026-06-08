<?php

// app/Services/TeamLicenseManager.php
namespace App\Services;

use App\Models\Team;
use App\Models\TeamLicense;
use App\Models\LicenseCode;
use Illuminate\Support\Facades\Auth;
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

    /**
     * Canjear un código de licencia generado por el Super Administrador.
     *
     *  - Código de meses  (type 'license') => activa licencia y devuelve mode 'license'
     *  - Código de semanas (type 'trial')  => activa periodo de prueba y devuelve mode 'trial'
     *
     * @return array{ok:bool, mode?:string, license?:TeamLicense, error?:string}
     */
    public function redeemCode(Team $team, string $rawCode): array
    {
        $code = LicenseCode::where('code', trim($rawCode))->first();

        if (! $code) {
            return ['ok' => false, 'error' => 'El código ingresado no es válido.'];
        }
        if (! $code->is_active) {
            return ['ok' => false, 'error' => 'Este código está desactivado.'];
        }
        if ($code->used_count >= $code->max_uses) {
            return ['ok' => false, 'error' => 'Este código ya fue utilizado.'];
        }

        $lic = TeamLicense::firstOrCreate(['team_id' => $team->id], ['is_active' => true]);

        if ($code->duration_unit === 'weeks') {
            // Periodo de prueba (semanas) -> Modo de Prueba activo
            $lic->trial_starts_at = now();
            $lic->trial_ends_at   = now()->addWeeks($code->duration_value);
            $lic->active_from     = null;
            $lic->active_until    = null;
            $mode = 'trial';
        } else {
            // Licencia (meses) -> Licencia activada
            $lic->active_from     = now();
            $lic->active_until    = now()->addMonths($code->duration_value);
            $lic->trial_starts_at = null;
            $lic->trial_ends_at   = null;
            $mode = 'license';
        }

        $lic->license_key = $code->code;
        $lic->is_active   = true;
        $lic->save();

        // Marca el uso del código
        $code->used_count++;
        if ($code->used_count >= $code->max_uses) {
            $code->redeemed_at          = now();
            $code->redeemed_by_team_id  = $team->id;
            $code->redeemed_by_user_id  = Auth::id();
        }
        $code->save();

        Cache::forget($this->cacheKey($team));

        return ['ok' => true, 'mode' => $mode, 'license' => $lic];
    }
}
