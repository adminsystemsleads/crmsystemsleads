<?php

namespace App\Policies;

use App\Models\Pipeline;
use App\Models\PipelineUserPermission;
use App\Models\User;

class PipelinePolicy
{
    protected function team($user)
    {
        return $user->currentTeam;
    }

    protected function isTeamOwner(User $user): bool
    {
        $team = $this->team($user);
        return $team && (int)$team->owner_id === (int)$user->id;
    }

    protected function isTeamAdmin(User $user): bool
    {
        $team = $this->team($user);

        // Jetstream: role "admin" (en team_user.role)
        if (class_exists(\Laravel\Jetstream\Jetstream::class) && $team) {
            return $user->hasTeamRole($team, 'admin');
        }

        // fallback opcional: users.is_admin
        if (property_exists($user, 'is_admin')) {
            return (bool) $user->is_admin;
        }

        return false;
    }

    protected function perm(User $user, Pipeline $pipeline): ?PipelineUserPermission
    {
        return PipelineUserPermission::where('pipeline_id', $pipeline->id)
            ->where('user_id', $user->id)
            ->first();
    }

    public function view(User $user, Pipeline $pipeline): bool
    {
        if ($this->isTeamOwner($user) || $this->isTeamAdmin($user)) return true;

        $p = $this->perm($user, $pipeline);
        return (bool) optional($p)->can_view;
    }

    public function edit(User $user, Pipeline $pipeline): bool
    {
        if ($this->isTeamOwner($user) || $this->isTeamAdmin($user)) return true;

        $p = $this->perm($user, $pipeline);
        return (bool) optional($p)->can_edit;
    }

    public function delete(User $user, Pipeline $pipeline): bool
    {
        if ($this->isTeamOwner($user) || $this->isTeamAdmin($user)) return true;

        $p = $this->perm($user, $pipeline);
        return (bool) optional($p)->can_delete;
    }

    public function configure(User $user, Pipeline $pipeline): bool
    {
        if ($this->isTeamOwner($user) || $this->isTeamAdmin($user)) return true;

        $p = $this->perm($user, $pipeline);
        return (bool) optional($p)->can_configure;
    }
}
