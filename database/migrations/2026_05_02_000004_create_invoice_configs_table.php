<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_configs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->unique();
            $table->string('ruc', 11);
            $table->string('razon_social', 250);
            $table->string('nombre_comercial', 250)->nullable();
            $table->string('ubigeo', 6)->default('150101');
            $table->string('departamento', 100)->default('LIMA');
            $table->string('provincia', 100)->default('LIMA');
            $table->string('distrito', 100)->default('LIMA');
            $table->string('urbanizacion', 100)->nullable();
            $table->string('direccion', 250)->default('-');
            $table->string('cod_pais', 2)->default('PE');
            $table->string('sol_user', 50)->nullable();
            $table->string('sol_password', 50)->nullable();
            $table->text('certificate_pem')->nullable();
            $table->enum('ambiente', ['beta', 'produccion'])->default('beta');
            $table->string('serie_factura', 4)->default('F001');
            $table->string('serie_boleta', 4)->default('B001');
            $table->unsignedInteger('next_factura')->default(1);
            $table->unsignedInteger('next_boleta')->default(1);
            $table->boolean('test_mode')->default(true); // simula SUNAT sin certificado real
            $table->timestamps();

            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_configs');
    }
};
