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
        Schema::table('categorias', function (Blueprint $table) {
            // Elimina el UNIQUE anterior sobre nombre
            // Opción A: si conoces el nombre exacto del índice:
            $table->dropUnique('categorias_nombre_unique');
            // Opción B: si no estás seguro, prueba:
            // $table->dropUnique(['nombre']); // Laravel generará el nombre

            // Crea UNIQUE compuesto (team_id, nombre)
            $table->unique(['team_id', 'nombre'], 'categorias_team_nombre_unique');
        });
    }

    public function down(): void
    {
        Schema::table('categorias', function (Blueprint $table) {
            $table->dropUnique('categorias_team_nombre_unique');
            $table->unique('nombre', 'categorias_nombre_unique');
        });
    }
};
