<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deal_activities', function (Blueprint $table) {
            // Lista de minutos antes del vencimiento para notificar, ej. [60,15,5].
            if (!Schema::hasColumn('deal_activities', 'notify_minutes')) {
                $table->json('notify_minutes')->nullable()->after('due_at');
            }
            // Umbrales ya notificados (para no repetir cada uno).
            if (!Schema::hasColumn('deal_activities', 'reminded_minutes')) {
                $table->json('reminded_minutes')->nullable()->after('notify_minutes');
            }
            // Ya no se usa el entero único.
            if (Schema::hasColumn('deal_activities', 'notify_before')) {
                $table->dropColumn('notify_before');
            }
        });
    }

    public function down(): void
    {
        Schema::table('deal_activities', function (Blueprint $table) {
            foreach (['notify_minutes', 'reminded_minutes'] as $col) {
                if (Schema::hasColumn('deal_activities', $col)) {
                    $table->dropColumn($col);
                }
            }
            if (!Schema::hasColumn('deal_activities', 'notify_before')) {
                $table->unsignedSmallInteger('notify_before')->nullable();
            }
        });
    }
};
