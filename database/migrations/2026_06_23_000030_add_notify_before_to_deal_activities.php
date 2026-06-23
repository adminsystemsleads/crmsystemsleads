<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deal_activities', function (Blueprint $table) {
            if (!Schema::hasColumn('deal_activities', 'notify_before')) {
                // Minutos antes del due_at para notificar. null/0 = sin notificación.
                $table->unsignedSmallInteger('notify_before')->nullable()->after('reminded_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('deal_activities', function (Blueprint $table) {
            if (Schema::hasColumn('deal_activities', 'notify_before')) {
                $table->dropColumn('notify_before');
            }
        });
    }
};
