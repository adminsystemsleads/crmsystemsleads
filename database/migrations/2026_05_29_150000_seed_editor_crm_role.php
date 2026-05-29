<?php

use App\Models\CrmRole;
use App\Models\Team;
use Illuminate\Database\Migrations\Migration;

/**
 * Agrega el rol "Editor" por defecto a cada team existente.
 * Idempotente: usa firstOrCreate, así que es seguro correr múltiples veces.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (Team::all() as $team) {
            CrmRole::seedDefaultsForTeam($team);
        }
    }

    public function down(): void
    {
        // Borra el rol Editor de todos los teams. Solo si nadie lo ha asignado a un user
        // (los profiles que lo tenían asignado quedarán con crm_role_id = null por la FK con nullOnDelete).
        CrmRole::where('name', 'Editor')->where('is_default', false)->delete();
    }
};
