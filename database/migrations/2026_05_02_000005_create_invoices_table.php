<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id');
            $table->unsignedBigInteger('deal_id')->nullable();
            $table->unsignedBigInteger('contact_id')->nullable();

            $table->string('tipo_doc', 2);           // '01'=factura,'03'=boleta
            $table->string('serie', 4);              // F001, B001
            $table->unsignedInteger('correlativo');

            $table->date('fecha_emision');
            $table->date('fecha_vencimiento')->nullable();
            $table->string('moneda', 3)->default('PEN');
            $table->decimal('igv_porcentaje', 5, 2)->default(18);

            $table->decimal('op_gravadas', 15, 2)->default(0);
            $table->decimal('op_exoneradas', 15, 2)->default(0);
            $table->decimal('op_inafectas', 15, 2)->default(0);
            $table->decimal('igv', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);

            // Snapshot del cliente al momento de emisión
            $table->string('cliente_tipo_doc', 2)->default('1');
            $table->string('cliente_num_doc', 15)->default('00000000');
            $table->string('cliente_razon_social', 250)->default('CLIENTE VARIOS');
            $table->string('cliente_direccion', 250)->nullable();

            // Estado y respuesta SUNAT
            $table->string('estado', 20)->default('draft'); // draft,signed,sent,accepted,rejected,cancelled
            $table->string('hash', 100)->nullable();
            $table->string('sunat_code', 20)->nullable();
            $table->text('sunat_description')->nullable();
            $table->text('sunat_notes')->nullable();

            $table->string('xml_path', 500)->nullable();
            $table->string('cdr_path', 500)->nullable();

            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->foreign('deal_id')->references('id')->on('deals')->onDelete('set null');
            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('set null');
            $table->index(['team_id', 'tipo_doc', 'serie', 'correlativo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
