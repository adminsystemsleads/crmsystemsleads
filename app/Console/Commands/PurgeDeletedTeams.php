<?php

namespace App\Console\Commands;

use App\Models\Team;
use Illuminate\Console\Command;

class PurgeDeletedTeams extends Command
{
    protected $signature = 'accounts:purge-deleted';

    protected $description = 'Elimina por completo (de la base de datos) las cuentas que llevan más de '
        . Team::PURGE_AFTER_DAYS . ' días eliminadas.';

    public function handle(): int
    {
        $cutoff = now()->subDays(Team::PURGE_AFTER_DAYS);

        // Solo las que aún no han sido purgadas y ya pasaron los 45 días.
        $teams = Team::onlyTrashed()
            ->whereNull('purged_at')
            ->where('deleted_at', '<=', $cutoff)
            ->get();

        foreach ($teams as $team) {
            $this->line("Eliminando permanentemente los datos de la cuenta #{$team->id} ({$team->name})");
            $team->purgeData();
        }

        $this->info("Cuentas eliminadas permanentemente: {$teams->count()}");

        return self::SUCCESS;
    }
}
