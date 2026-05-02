<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_ai_assistants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('whatsapp_account_id')->constrained('whatsapp_accounts')->cascadeOnDelete();
            $table->string('provider')->default('openai');
            $table->string('model')->default('gpt-4o-mini');
            $table->text('api_key');
            $table->text('system_prompt')->nullable();
            $table->decimal('temperature', 3, 2)->default(0.70);
            $table->unsignedSmallInteger('max_tokens')->default(500);
            $table->unsignedSmallInteger('context_messages')->default(20);
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->unique('whatsapp_account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_ai_assistants');
    }
};
