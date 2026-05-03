<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->string('cod_producto', 50)->default('ZZZ9999999AA');
            $table->string('descripcion', 250);
            $table->string('unidad', 10)->default('NIU'); // NIU=unidad,ZZ=servicio,KGM=kg
            $table->decimal('cantidad', 12, 2)->default(1);
            $table->decimal('precio_unitario', 15, 2)->default(0); // con IGV
            $table->decimal('valor_unitario', 15, 2)->default(0);  // sin IGV
            $table->string('tip_afe_igv', 2)->default('10'); // 10=gravado,20=exonerado,30=inafecto
            $table->decimal('igv_porcentaje', 5, 2)->default(18);
            $table->decimal('igv', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);  // con IGV
            $table->timestamps();

            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            $table->index('invoice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
