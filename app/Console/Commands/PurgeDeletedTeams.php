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

        $teams = Team::onlyTrashed()
            ->where('deleted_at', '<=', $cutoff)
            ->get();

        foreach ($teams as $team) {
            $this->line("Eliminando definitivamente la cuenta #{$team->id} ({$team->name})");
            $team->forceDelete();
        }

        $this->info("Cuentas eliminadas por completo: {$teams->count()}");

        return self::SUCCESS;
    }
}
