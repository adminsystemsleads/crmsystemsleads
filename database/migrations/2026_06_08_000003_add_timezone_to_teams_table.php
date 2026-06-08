<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Zona horaria de la cuenta (equipo). Se usa para que el vencimiento de las
 * licencias caiga a las 23:59 del día correspondiente en la hora del cliente.
 * Por defecto America/Lima (GMT-5).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('teams', 'timezone')) {
            Schema::table('teams', function (Blueprint $table) {
                $table->string('timezone')->default('America/Lima')->after('name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('teams', 'timezone')) {
            Schema::table('teams', function (Blueprint $table) {
                $table->dropColumn('timezone');
            });
        }
    }
};
