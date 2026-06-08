<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Marca de Super Administrador a nivel de plataforma (no por team).
 * Solo el correo definido tendrá acceso al área "Generar Códigos de Licencia".
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'is_super_admin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_super_admin')->default(false)->after('email');
            });
        }

        // Asigna el rol de Super Administrador al correo indicado (si ya existe).
        DB::table('users')
            ->where('email', 'admin@systemsleads.com')
            ->update(['is_super_admin' => true]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'is_super_admin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_super_admin');
            });
        }
    }
};
