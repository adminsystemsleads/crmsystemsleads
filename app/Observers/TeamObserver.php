<?php
// app/Observers/TeamObserver.php
namespace App\Observers;

use App\Models\Team;
use App\Models\TeamLicense;
use App\Services\TeamLicenseManager;

class TeamObserver
{
    /**
     * Al crear un equipo (cuenta) se habilita automáticamente un periodo de prueba:
     *  - 15 días: si es la PRIMERA cuenta de ese usuario (recién registrado).
     *  - 7 días : si el usuario ya tenía otra cuenta (no es un usuario nuevo).
     */
    public function created(Team $team): void
    {
        // ¿Cuántas cuentas posee ya este usuario? (incluye la recién creada)
        $ownedCount = Team::where('user_id', $team->user_id)->count();

        $days = $ownedCount <= 1 ? 15 : 7;

        // El periodo vence a las 23:59 del último día, en la zona horaria de la cuenta.
        $tz   = $team->effectiveTimezone();
        $ends = TeamLicenseManager::endOfDayForStorage(now()->setTimezone($tz)->addDays($days));

        TeamLicense::firstOrCreate(
            ['team_id' => $team->id],
            [
                'trial_starts_at' => now(),
                'trial_ends_at'   => $ends,
                'grant_type'      => 'trial',
                'is_active'       => true,
            ]
        );
    }
}
