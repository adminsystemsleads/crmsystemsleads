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
        Schema::create('gasto_mensuales', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('team_id')->constrained()->onDelete('cascade');

    // Relación con categoría
    $table->foreignId('categoria_id')
      ->constrained('categorias')
      ->restrictOnDelete(); // o ->onDelete('restrict')


    $table->string('mes');
    $table->integer('año');
    $table->string('codigopago')->nullable();
    $table->date('dia_pago')->nullable();
    $table->string('link_vaucher')->nullable();
    $table->decimal('monto_pagar', 10, 2)->default(0);
    $table->boolean('pago_verificado')->default(false);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gasto_mensuales');
    }
};
