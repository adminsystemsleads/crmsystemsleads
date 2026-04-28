<?php

// database/migrations/xxxx_xx_xx_xxxxxx_add_team_member_profile_id_to_gasto_mensuales.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('gasto_mensuales', function (Blueprint $table) {
            $table->foreignId('team_member_profile_id')
                  ->nullable()
                  ->after('team_id')
                  ->constrained('team_member_profiles')
                  ->nullOnDelete(); // si se borra el perfil, el gasto queda sin perfil
        });
    }

    public function down(): void
    {
        Schema::table('gasto_mensuales', function (Blueprint $table) {
            $table->dropForeign(['team_member_profile_id']);
            $table->dropColumn('team_member_profile_id');
        });
    }
};
