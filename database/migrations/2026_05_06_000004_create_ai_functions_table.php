<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_functions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id');
            $table->unsignedBigInteger('whatsapp_ai_assistant_id');
            $table->string('mode', 30)->default('update_crm'); // update_crm, change_stage, info
            $table->string('name', 60);                          // ej: "save_lead_data" (snake_case)
            $table->text('description');                         // cuándo activarse (instruction)
            $table->json('properties')->nullable();              // campos CRM seleccionados
            $table->unsignedBigInteger('target_stage_id')->nullable(); // para mode=change_stage
            $table->text('response_template')->nullable();       // texto que enviará el bot al ejecutar
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->foreign('whatsapp_ai_assistant_id', 'aifn_asst_fk')
                ->references('id')->on('whatsapp_ai_assistants')->onDelete('cascade');
            $table->foreign('target_stage_id')->references('id')->on('pipeline_stages')->onDelete('set null');
            $table->index('whatsapp_ai_assistant_id', 'aifn_asst_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_functions');
    }
};
