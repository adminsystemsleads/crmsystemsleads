<?php
// app/Observers/TeamObserver.php
namespace App\Observers;

use App\Models\Team;
use App\Services\TeamLicenseManager;
use Illuminate\Support\Facades\Log;

class TeamObserver
{
    /**
     * Al crear un equipo (cuenta) se habilita automáticamente un periodo de prueba:
     *  - 15 días: si es la PRIMERA cuenta de ese usuario (recién registrado).
     *  - 7 días : si el usuario ya tenía otra cuenta (no es un usuario nuevo).
     *
     * Se envuelve en try/catch para que un fallo al crear la licencia NUNCA
     * rompa la creación de la cuenta ni la deje en estado inconsistente; si por
     * algún motivo no se crea aquí, el middleware la autorrecupera al primer acceso.
     */
    public function created(Team $team): void
    {
        try {
            app(TeamLicenseManager::class)->ensureTrial($team);
        } catch (\Throwable $e) {
            Log::error('No se pudo crear la prueba inicial del equipo', [
                'team_id' => $team->id,
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
