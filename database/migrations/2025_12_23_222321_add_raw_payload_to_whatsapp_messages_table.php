<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::table('whatsapp_messages', function (Blueprint $table) {
    if (!Schema::hasColumn('whatsapp_messages', 'raw_payload')) {
        $table->longText('raw_payload')->nullable()->after('body');
    }
    if (!Schema::hasColumn('whatsapp_messages', 'message_id')) {
        $table->string('message_id', 512)->nullable()->index();
    } else {
        // si existe pero es corto, lo ideal es alterar manualmente a 512 o text (según tu setup)
    }
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('whatsapp_messages', function (Blueprint $table) {
            //
        });
    }
};
