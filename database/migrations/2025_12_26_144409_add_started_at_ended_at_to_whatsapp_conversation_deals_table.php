<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('whatsapp_conversation_deals', function (Blueprint $table) {
            if (!Schema::hasColumn('whatsapp_conversation_deals', 'started_at')) {
                $table->timestamp('started_at')->nullable()->after('deal_id');
            }
            if (!Schema::hasColumn('whatsapp_conversation_deals', 'ended_at')) {
                $table->timestamp('ended_at')->nullable()->after('started_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_conversation_deals', function (Blueprint $table) {
            if (Schema::hasColumn('whatsapp_conversation_deals', 'started_at')) {
                $table->dropColumn('started_at');
            }
            if (Schema::hasColumn('whatsapp_conversation_deals', 'ended_at')) {
                $table->dropColumn('ended_at');
            }
        });
    }
};
