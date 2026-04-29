<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Jetstream\Events\TeamCreated;
use Laravel\Jetstream\Events\TeamDeleted;
use Laravel\Jetstream\Events\TeamUpdated;
use Laravel\Jetstream\Team as JetstreamTeam;

class Team extends JetstreamTeam
{
    /** @use HasFactory<\Database\Factories\TeamFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'personal_team',
        'settings',
    ];

    // Modules enabled by default when no settings exist yet
    protected array $defaultModules = [
        'crm'              => true,
        'contactos'        => true,
        'whatsapp_inbox'   => true,
        'whatsapp_cuentas' => true,
        'finanzas'         => false,
        'pagos'            => false,
        'transparencia_ia' => false,
        'perfil_unidad'    => false,
        'gastos'           => false,
        'gastos_import'    => false,
        'perfiles'         => false,
        'categorias'       => false,
    ];

    public function moduleEnabled(string $module): bool
    {
        $modules = ($this->settings ?? [])['modules'] ?? [];
        return $modules[$module] ?? ($this->defaultModules[$module] ?? false);
    }

    /**
     * The event map for the model.
     *
     * @var array<string, class-string>
     */
    protected $dispatchesEvents = [
        'created' => TeamCreated::class,
        'updated' => TeamUpdated::class,
        'deleted' => TeamDeleted::class,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'personal_team' => 'boolean',
            'settings'      => 'array',
        ];
    }

      public function license()
    {
        return $this->hasOne(TeamLicense::class); // FK: team_id
    }
}
