<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('whatsapp_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('whatsapp_messages', 'sent_by_user_id')) {
                $table->unsignedBigInteger('sent_by_user_id')->nullable()->after('raw_payload');
                $table->index('sent_by_user_id');

                // FK opcional (si tu tabla users es la estándar)
                $table->foreign('sent_by_user_id')
                    ->references('id')->on('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_messages', function (Blueprint $table) {
            if (Schema::hasColumn('whatsapp_messages', 'sent_by_user_id')) {
                // primero FK si existe
                try { $table->dropForeign(['sent_by_user_id']); } catch (\Throwable $e) {}
                $table->dropIndex(['sent_by_user_id']);
                $table->dropColumn('sent_by_user_id');
            }
        });
    }
};
