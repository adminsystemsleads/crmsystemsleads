<?php
// app/Observers/TeamObserver.php
namespace App\Observers;

use App\Models\Team;
use App\Models\TeamLicense;

class TeamObserver
{
    public function created(Team $team): void
    {
        TeamLicense::firstOrCreate(
            ['team_id'=>$team->id],
            [
                'trial_starts_at' => now(),
                'trial_ends_at'   => now()->addDays(30),
                'is_active'       => true,
            ]
        );
    }
}
