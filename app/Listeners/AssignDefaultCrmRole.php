<?php

namespace App\Listeners;

use App\Models\CrmRole;
use App\Models\TeamMemberProfile;
use Laravel\Jetstream\Events\TeamMemberAdded;

class AssignDefaultCrmRole
{
    /**
     * Al agregar un usuario a una cuenta existente, se le asigna por defecto
     * el rol de CRM "Editor". Si ya tenía un perfil/rol, no lo sobreescribe.
     */
    public function handle(TeamMemberAdded $event): void
    {
        $team = $event->team;
        $user = $event->user;

        if (! $team || ! $user) {
            return;
        }

        // Garantiza que existan los roles del sistema (Administrador + Editor).
        CrmRole::seedDefaultsForTeam($team);

        $editor = CrmRole::where('team_id', $team->id)
            ->where('name', 'Editor')
            ->first();

        TeamMemberProfile::firstOrCreate(
            ['team_id' => $team->id, 'user_id' => $user->id],
            ['correo' => $user->email, 'crm_role_id' => $editor?->id]
        );
    }
}
