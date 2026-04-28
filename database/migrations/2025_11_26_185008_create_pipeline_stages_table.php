<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_pipeline_stages_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pipeline_stages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pipeline_id')
                ->constrained('pipelines')
                ->onDelete('cascade');

            $table->string('name');     // "Nuevo", "En negociación", etc.
            $table->string('slug');     // "new", "negotiation" (interno)
            $table->integer('sort_order')->default(0);

            // Para reportes de probabilidad
            $table->unsignedTinyInteger('probability')->nullable(); // 0–100

            // Para marcar si la etapa significa ganar o perder
            $table->boolean('is_won')->default(false);
            $table->boolean('is_lost')->default(false);

            $table->timestamps();

            $table->unique(['pipeline_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pipeline_stages');
    }
};

