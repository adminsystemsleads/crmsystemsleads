<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            // Lista de responsables; si hay más de uno, el reparto es aleatorio y equitativo.
            $table->json('assigned_user_ids')->nullable()->after('assigned_user_id');
        });

        // Conserva el responsable único previo copiándolo a la nueva lista.
        DB::table('forms')->whereNotNull('assigned_user_id')->update([
            'assigned_user_ids' => DB::raw('JSON_ARRAY(assigned_user_id)'),
        ]);
    }

    public function down(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->dropColumn('assigned_user_ids');
        });
    }
};
