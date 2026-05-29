<?php

namespace App\Models;

use App\Support\CrmPermissions;
use Illuminate\Database\Eloquent\Model;

class CrmRole extends Model
{
    protected $fillable = [
        'team_id',
        'name',
        'description',
        'is_default',
        'permissions',
    ];

    protected $casts = [
        'is_default'  => 'boolean',
        'permissions' => 'array',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function hasPermission(string $key): bool
    {
        return in_array($key, (array) ($this->permissions ?? []), true);
    }

    /**
     * Cantidad de permisos asignados (para mostrar en la lista).
     */
    public function getPermissionCountAttribute(): int
    {
        return count((array) ($this->permissions ?? []));
    }

    /**
     * Asigna TODOS los permisos del catálogo. Útil para crear el rol "Administrador".
     */
    public function grantAll(): self
    {
        $this->permissions = CrmPermissions::allKeys();
        return $this;
    }

    /**
     * Crea los roles del sistema por defecto en un team (Administrador + Editor).
     * Idempotente: si ya existen, no los duplica.
     */
    public static function seedDefaultsForTeam(Team $team): void
    {
        self::firstOrCreate(
            ['team_id' => $team->id, 'name' => 'Administrador'],
            [
                'description' => 'Acceso total a todas las herramientas del CRM. Rol creado por el sistema.',
                'is_default'  => true,
                'permissions' => CrmPermissions::allKeys(),
            ]
        );

        self::firstOrCreate(
            ['team_id' => $team->id, 'name' => 'Editor'],
            [
                'description' => 'Puede ver, crear y editar. Sin permisos de eliminar ni de administración.',
                'is_default'  => false,
                'permissions' => CrmPermissions::editorDefaultKeys(),
            ]
        );
    }
}
