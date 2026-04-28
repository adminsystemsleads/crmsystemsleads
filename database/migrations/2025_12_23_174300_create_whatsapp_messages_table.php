<?php

// database/migrations/xxxx_xx_xx_create_whatsapp_messages_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->index();
            $table->unsignedBigInteger('whatsapp_account_id')->index();
            $table->unsignedBigInteger('whatsapp_conversation_id')->index();

            $table->string('direction')->index(); // in/out
            $table->string('message_id')->nullable()->unique(); // Meta msg id (idempotencia)
            $table->string('type')->default('text');
            $table->longText('body')->nullable();
            $table->json('payload')->nullable();

            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->foreign('team_id')->references('id')->on('teams')->cascadeOnDelete();
            $table->foreign('whatsapp_account_id')->references('id')->on('whatsapp_accounts')->cascadeOnDelete();
            $table->foreign('whatsapp_conversation_id')->references('id')->on('whatsapp_conversations')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
    }
};
