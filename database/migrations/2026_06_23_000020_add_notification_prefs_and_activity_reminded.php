<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'notification_prefs')) {
                $table->json('notification_prefs')->nullable()->after('email');
            }
        });

        Schema::table('deal_activities', function (Blueprint $table) {
            if (!Schema::hasColumn('deal_activities', 'reminded_at')) {
                $table->timestamp('reminded_at')->nullable()->after('due_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'notification_prefs')) {
                $table->dropColumn('notification_prefs');
            }
        });

        Schema::table('deal_activities', function (Blueprint $table) {
            if (Schema::hasColumn('deal_activities', 'reminded_at')) {
                $table->dropColumn('reminded_at');
            }
        });
    }
};
