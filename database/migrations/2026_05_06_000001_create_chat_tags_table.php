<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_tags', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id');
            $table->string('name', 80);
            $table->string('color', 20)->default('indigo'); // tailwind color name
            $table->timestamps();

            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->unique(['team_id', 'name']);
        });

        Schema::create('whatsapp_conversation_tag', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('whatsapp_conversation_id');
            $table->unsignedBigInteger('chat_tag_id');
            $table->timestamps();

            $table->foreign('whatsapp_conversation_id', 'wct_conv_fk')
                ->references('id')->on('whatsapp_conversations')->onDelete('cascade');
            $table->foreign('chat_tag_id', 'wct_tag_fk')
                ->references('id')->on('chat_tags')->onDelete('cascade');
            $table->unique(['whatsapp_conversation_id', 'chat_tag_id'], 'wct_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_conversation_tag');
        Schema::dropIfExists('chat_tags');
    }
};
