<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tipo del último otorgamiento de la licencia: 'license' (meses),
 * 'trial' (semanas) o 'prorroga' (días). Permite identificar qué cuentas
 * están en periodo de prórroga o se les venció (cuenta bloqueada).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('team_licenses', 'grant_type')) {
            Schema::table('team_licenses', function (Blueprint $table) {
                $table->string('grant_type')->nullable()->after('license_key');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('team_licenses', 'grant_type')) {
            Schema::table('team_licenses', function (Blueprint $table) {
                $table->dropColumn('grant_type');
            });
        }
    }
};
