<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();

            $table->string('name');                 // nombre interno
            $table->string('slug', 40)->unique();   // token público del link

            // Contenido visible
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->string('button_text')->default('Enviar');
            $table->text('success_message')->nullable();
            $table->string('redirect_url')->nullable();

            // Diseño
            $table->string('bg_color', 9)->default('#f3f4f6');
            $table->string('card_color', 9)->default('#ffffff');
            $table->string('text_color', 9)->default('#1f2937');
            $table->string('primary_color', 9)->default('#4f46e5');
            $table->string('button_text_color', 9)->default('#ffffff');

            // Destino de la negociación
            $table->foreignId('pipeline_id')->nullable()->constrained('pipelines')->nullOnDelete();
            $table->foreignId('stage_id')->nullable()->constrained('pipeline_stages')->nullOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('deal_title_template')->nullable(); // ej: "{form} - {name}"

            // Manejo de duplicados de negociación
            $table->enum('deal_dedup_mode', ['always_create', 'use_active'])->default('always_create');
            // Cuando se reutiliza la negociación activa, opcionalmente moverla a esta etapa
            $table->foreignId('move_stage_id')->nullable()->constrained('pipeline_stages')->nullOnDelete();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('team_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forms');
    }
};
