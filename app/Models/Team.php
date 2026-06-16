<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Laravel\Jetstream\Events\TeamCreated;
use Laravel\Jetstream\Events\TeamDeleted;
use Laravel\Jetstream\Events\TeamUpdated;
use Laravel\Jetstream\Team as JetstreamTeam;

class Team extends JetstreamTeam
{
    /** @use HasFactory<\Database\Factories\TeamFactory> */
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'timezone',
        'personal_team',
        'settings',
    ];

    /** Zona horaria por defecto (GMT-5). */
    public const DEFAULT_TIMEZONE = 'America/Lima';

    /** Días que una cuenta eliminada permanece antes de borrarse por completo. */
    public const PURGE_AFTER_DAYS = 45;

    /** ¿La cuenta fue eliminada permanentemente (datos purgados)? */
    public function isPurged(): bool
    {
        return $this->purged_at !== null;
    }

    /**
     * Días que faltan para que una cuenta eliminada se purgue definitivamente.
     * Null si no está eliminada o si ya fue purgada.
     */
    public function daysUntilPurge(): ?int
    {
        if (! $this->trashed() || ! $this->deleted_at || $this->isPurged()) {
            return null;
        }

        $elapsed = (int) $this->deleted_at->diffInDays(now());

        return max(0, self::PURGE_AFTER_DAYS - $elapsed);
    }

    /**
     * Tablas con team_id, ordenadas para que al borrar no rompan llaves foráneas
     * (hijos antes que padres; las sub-tablas sin team_id se borran por cascada).
     */
    private const PURGE_TABLES = [
        'whatsapp_messages', 'whatsapp_conversations', 'whatsapp_ai_assistants', 'whatsapp_accounts',
        'payments', 'team_subscriptions', 'invoices', 'deals', 'pipelines', 'contacts', 'products',
        'invoice_configs', 'custom_fields', 'categorias', 'gasto_mensuales', 'chat_tags', 'quick_replies',
        'ai_functions', 'ai_knowledge_entries', 'team_member_profiles', 'crm_roles', 'team_invitations',
        'team_licenses',
    ];

    /**
     * Elimina permanentemente TODOS los datos de la cuenta para liberar recursos,
     * pero conserva la fila del equipo como registro marcado con purged_at.
     */
    public function purgeData(): void
    {
        $id = $this->id;

        DB::transaction(function () use ($id) {
            foreach (self::PURGE_TABLES as $table) {
                DB::table($table)->where('team_id', $id)->delete();
            }
            // Marca como eliminada permanentemente (conserva la fila del equipo).
            DB::table('teams')->where('id', $id)->update(['purged_at' => now()]);
        });

        $this->purged_at = now();
    }

    /** Zona horaria efectiva del equipo (con respaldo al valor por defecto). */
    public function effectiveTimezone(): string
    {
        return $this->timezone ?: self::DEFAULT_TIMEZONE;
    }

    // Modules enabled by default when no settings exist yet
    protected array $defaultModules = [
        'dashboard'        => true,
        'crm'              => true,
        'contactos'        => true,
        'whatsapp_inbox'   => true,
        'whatsapp_cuentas' => true,
        'finanzas'         => false,
        'pagos'            => false,
        'transparencia_ia' => false,
        'perfil_unidad'    => true,
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
            'purged_at'     => 'datetime',
        ];
    }

      public function license()
    {
        return $this->hasOne(TeamLicense::class); // FK: team_id
    }
}
