<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gasto_mensuales', function (Blueprint $table) {
            // Convertir mes a número entero (1–12)
            $table->unsignedTinyInteger('mes')->change();

            // Convertir año a entero pequeño (4 dígitos, 2000–9999)
            $table->unsignedSmallInteger('año')->change();
        });
    }

    public function down(): void
    {
        Schema::table('gasto_mensuales', function (Blueprint $table) {
            // Volver a los tipos originales si haces rollback
            $table->string('mes', 255)->change();
            $table->integer('año')->change();
        });
    }
};
