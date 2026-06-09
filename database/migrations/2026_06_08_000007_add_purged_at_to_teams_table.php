<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Marca de "eliminada permanentemente": cuando una cuenta se purga (manual o por
 * la retención de 45 días) se borran TODOS sus datos para liberar recursos, pero
 * se conserva la fila del equipo como registro (tombstone) para que siga
 * apareciendo en el Reporte de Cuentas con estado "Eliminada permanentemente".
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('teams', 'purged_at')) {
            Schema::table('teams', function (Blueprint $table) {
                $table->timestamp('purged_at')->nullable()->after('deleted_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('teams', 'purged_at')) {
            Schema::table('teams', function (Blueprint $table) {
                $table->dropColumn('purged_at');
            });
        }
    }
};
