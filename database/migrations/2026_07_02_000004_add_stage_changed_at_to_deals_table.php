<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            // Momento en que la negociación entró a su etapa actual (para ordenar el Kanban).
            $table->timestamp('stage_changed_at')->nullable()->after('stage_id');
        });

        // Backfill: usa la fecha de creación para las negociaciones existentes.
        DB::table('deals')->update(['stage_changed_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->dropColumn('stage_changed_at');
        });
    }
};
