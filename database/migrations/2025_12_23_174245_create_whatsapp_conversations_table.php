<?php

// database/migrations/xxxx_xx_xx_create_whatsapp_conversations_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('whatsapp_conversations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->index();
            $table->unsignedBigInteger('whatsapp_account_id')->index();

            $table->string('wa_id')->index(); // nro cliente (ej 51999...)
            $table->unsignedBigInteger('contact_id')->nullable()->index();
            $table->unsignedBigInteger('deal_id')->nullable()->index(); // deal actual activo

            $table->unsignedBigInteger('assigned_to')->nullable()->index();
            $table->unsignedInteger('unread_count')->default(0);

            $table->timestamp('last_message_at')->nullable();
            $table->string('status')->default('open'); // open/closed
            $table->timestamps();

            $table->unique(['whatsapp_account_id', 'wa_id']);

            $table->foreign('team_id')->references('id')->on('teams')->cascadeOnDelete();
            $table->foreign('whatsapp_account_id')->references('id')->on('whatsapp_accounts')->cascadeOnDelete();
            $table->foreign('contact_id')->references('id')->on('contacts')->nullOnDelete();
            $table->foreign('deal_id')->references('id')->on('deals')->nullOnDelete();
            $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_conversations');
    }
};
