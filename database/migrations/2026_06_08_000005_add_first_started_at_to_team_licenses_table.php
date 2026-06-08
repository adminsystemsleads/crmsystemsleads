<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fecha en que la cuenta obtuvo su PRIMERA licencia/periodo (no cambia nunca).
 * Sirve para el reporte de cuentas, además de la fecha de inicio/fin actual.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('team_licenses', 'first_started_at')) {
            Schema::table('team_licenses', function (Blueprint $table) {
                $table->timestamp('first_started_at')->nullable()->after('grant_type');
            });

            // Backfill para licencias existentes.
            DB::table('team_licenses')->update([
                'first_started_at' => DB::raw('COALESCE(trial_starts_at, active_from, created_at)'),
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('team_licenses', 'first_started_at')) {
            Schema::table('team_licenses', function (Blueprint $table) {
                $table->dropColumn('first_started_at');
            });
        }
    }
};
