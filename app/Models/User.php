<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use HasTeams;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'country_code',
        'phone',
        'is_super_admin',
    ];

    /**
     * Correo con privilegios de Super Administrador de plataforma.
     * Sirve como respaldo aunque la columna is_super_admin se reinicie.
     */
    public const SUPER_ADMIN_EMAIL = 'admin@systemsleads.com';

    /**
     * ¿Es Super Administrador de la plataforma? (acceso a "Generar Códigos de Licencia")
     */
    public function isSuperAdmin(): bool
    {
        return (bool) ($this->is_super_admin ?? false)
            || strcasecmp((string) $this->email, self::SUPER_ADMIN_EMAIL) === 0;
    }

    /**
     * Rol de CRM del usuario en un team (vía su perfil de miembro).
     */
    public function crmRoleFor($team): ?\App\Models\CrmRole
    {
        if (! $team) {
            return null;
        }

        return optional(
            \App\Models\TeamMemberProfile::where('team_id', $team->id)
                ->where('user_id', $this->id)
                ->first()
        )->crmRole;
    }

    /**
     * ¿Es administrador de este team? True si es dueño/admin de Jetstream
     * o si su rol de CRM tiene permisos de administración (rol "Administrador").
     * Así, cambiar el rol de CRM a Administrador habilita la vista de admin.
     */
    public function isCrmAdminFor($team): bool
    {
        if (! $team) {
            return false;
        }

        if ((int) $team->user_id === (int) $this->id || $this->hasTeamRole($team, 'admin')) {
            return true;
        }

        $role = $this->crmRoleFor($team);

        return $role && $role->hasPermission('admin.manage_team');
    }

    /**
     * ¿Puede ver este embudo?
     *  - Dueño/admin del team y roles con 'pipelines.view_all' (Administrador, Editor)
     *    ven TODOS los embudos (incluidos los nuevos).
     *  - Otros roles solo ven los embudos marcados en allowed_pipeline_ids.
     */
    public function canViewPipeline(\App\Models\Pipeline $pipeline): bool
    {
        $team = $pipeline->team;

        if ($team && ((int) $team->user_id === (int) $this->id || $this->hasTeamRole($team, 'admin'))) {
            return true;
        }

        $role = $this->crmRoleFor($team);
        if (! $role) {
            return false;
        }

        return $role->canViewPipeline($pipeline->id);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
