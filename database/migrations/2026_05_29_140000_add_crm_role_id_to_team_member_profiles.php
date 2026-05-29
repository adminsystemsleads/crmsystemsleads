<?php

use App\Models\CrmRole;
use App\Models\Team;
use App\Models\TeamMemberProfile;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_member_profiles', function (Blueprint $table) {
            // nullable porque los miembros nuevos pueden no tener rol aún
            $table->foreignId('crm_role_id')
                  ->nullable()
                  ->after('user_id')
                  ->constrained('crm_roles')
                  ->nullOnDelete();
        });

        // Backfill: a cada team owner le asignamos el rol "Administrador" por defecto.
        foreach (Team::all() as $team) {
            $adminRole = CrmRole::where('team_id', $team->id)
                ->where('is_default', true)
                ->first();

            if (! $adminRole) continue;

            $profile = TeamMemberProfile::firstOrCreate(
                ['team_id' => $team->id, 'user_id' => $team->user_id],
                ['correo' => optional($team->owner)->email]
            );

            if (! $profile->crm_role_id) {
                $profile->update(['crm_role_id' => $adminRole->id]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('team_member_profiles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('crm_role_id');
        });
    }
};
