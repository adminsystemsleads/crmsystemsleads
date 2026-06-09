<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Soft delete para equipos (cuentas). Al eliminar una cuenta no se borra de la
 * base: se marca con deleted_at, así sigue apareciendo en el Reporte de Cuentas
 * del Super Administrador con estado "Eliminada" y su fecha de eliminación.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('teams', 'deleted_at')) {
            Schema::table('teams', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('teams', 'deleted_at')) {
            Schema::table('teams', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
