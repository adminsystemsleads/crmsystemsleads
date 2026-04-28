<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('whatsapp_conversations', function (Blueprint $table) {
            if (!Schema::hasColumn('whatsapp_conversations', 'contact_name')) {
                $table->string('contact_name')->nullable()->after('wa_id');
            }
            if (!Schema::hasColumn('whatsapp_conversations', 'contact_phone')) {
                $table->string('contact_phone')->nullable()->after('contact_name');
            }
            if (!Schema::hasColumn('whatsapp_conversations', 'status')) {
                $table->string('status')->default('open')->after('contact_phone');
            }
            if (!Schema::hasColumn('whatsapp_conversations', 'last_message_at')) {
                $table->timestamp('last_message_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('whatsapp_conversations', 'last_message_preview')) {
                $table->string('last_message_preview', 255)->nullable()->after('last_message_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_conversations', function (Blueprint $table) {
            foreach ([
                'contact_name',
                'contact_phone',
                'status',
                'last_message_at',
                'last_message_preview'
            ] as $col) {
                if (Schema::hasColumn('whatsapp_conversations', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
