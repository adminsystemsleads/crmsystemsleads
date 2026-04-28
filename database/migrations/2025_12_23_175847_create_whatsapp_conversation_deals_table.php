<?php

// database/migrations/xxxx_xx_xx_create_whatsapp_conversation_deals_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('whatsapp_conversation_deals', function (Blueprint $table) {
    $table->id();

    $table->unsignedBigInteger('whatsapp_conversation_id');
    $table->unsignedBigInteger('deal_id');

    $table->timestamps();

    $table->foreign('whatsapp_conversation_id')
        ->references('id')->on('whatsapp_conversations')
        ->onDelete('cascade');

    $table->foreign('deal_id')
        ->references('id')->on('deals')
        ->onDelete('cascade');

    // 👇 nombre corto para MySQL
    $table->unique(['whatsapp_conversation_id', 'deal_id'], 'wa_conv_deal_unique');
});

    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_conversation_deals');
    }
};
