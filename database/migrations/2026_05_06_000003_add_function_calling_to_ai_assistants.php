<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_ai_assistants', function (Blueprint $table) {
            $table->boolean('function_calling_enabled')->default(false)->after('is_active');
            // Lista de campos que el bot puede capturar/actualizar (contacto + deal + custom)
            $table->json('capture_config')->nullable()->after('function_calling_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_ai_assistants', function (Blueprint $table) {
            $table->dropColumn(['function_calling_enabled', 'capture_config']);
        });
    }
};
