<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deal_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('deal_id');
            $table->unsignedBigInteger('product_id')->nullable(); // null = línea libre
            $table->string('name');                               // copiado o libre
            $table->string('unit', 50)->default('unidad');
            $table->decimal('quantity', 12, 2)->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('discount', 5, 2)->default(0);       // porcentaje 0-100
            $table->decimal('total', 15, 2)->default(0);         // calculado
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('deal_id')->references('id')->on('deals')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
            $table->index('deal_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deal_products');
    }
};
