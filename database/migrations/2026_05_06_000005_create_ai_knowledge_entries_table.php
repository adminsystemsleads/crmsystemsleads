<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_knowledge_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id');
            $table->unsignedBigInteger('whatsapp_ai_assistant_id');
            $table->string('source', 20)->default('text');   // text, file
            $table->string('title', 200);
            $table->string('original_filename', 255)->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedInteger('size_bytes')->default(0);
            $table->longText('content');                       // texto extraído / pegado
            $table->string('storage_path', 500)->nullable();   // si es archivo
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->foreign('whatsapp_ai_assistant_id', 'aikb_asst_fk')
                ->references('id')->on('whatsapp_ai_assistants')->onDelete('cascade');
            $table->index('whatsapp_ai_assistant_id', 'aikb_asst_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_knowledge_entries');
    }
};
