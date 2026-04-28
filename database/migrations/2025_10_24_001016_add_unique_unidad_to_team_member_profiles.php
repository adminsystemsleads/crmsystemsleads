<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('team_member_profiles', function (Blueprint $table) {
    $table->unique(['team_id', 'unidad'], 'unique_unidad_por_team');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('team_member_profiles', function (Blueprint $table) {
    $table->dropUnique('unique_unidad_por_team');
});
    }
};
