<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pipeline extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'name',
        'description',
        'is_default',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active'  => 'boolean',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function stages()
    {
        return $this->hasMany(PipelineStage::class)->orderBy('sort_order');
    }

    public function deals()
    {
        return $this->hasMany(Deal::class);
    }

    public function permissions()
{
    return $this->hasMany(\App\Models\PipelineUserPermission::class);
}

public function permissionsForUser($user)
{
    return $this->permissions()
        ->where('user_id', $user->id)
        ->first();
}

/**
 * Reglas de negocio:
 * - Si el usuario es admin/owner del team → siempre tiene todo.
 * - Si no, miramos la fila en pipeline_user_permissions.
 */
public function userCan($user, string $ability): bool
{
    $team = $this->team; // asumiendo relación $pipeline->team

    // 1) Owner del team siempre puede
    if ($team && $team->owner_id === $user->id) {
        return true;
    }

    // 2) Si usas roles de Jetstream, aquí podrías hacer:
    // if ($team && $team->userHasRole($user, 'admin')) return true;
    // (ajusta según tu implementación de roles en team_user)

    $perm = $this->permissionsForUser($user);
    if (!$perm) {
        // Sin registro => por defecto solo ver si can_view está pensado así
        return $ability === 'view';
    }

    return match ($ability) {
        'view'       => (bool) $perm->can_view,
        'edit'       => (bool) $perm->can_edit,
        'delete'     => (bool) $perm->can_delete,
        'configure'  => (bool) $perm->can_configure,
        default      => false,
    };
}

}
