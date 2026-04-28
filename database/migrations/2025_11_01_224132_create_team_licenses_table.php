<?php

// database/migrations/2025_11_01_000000_create_team_licenses_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('team_licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('license_key')->nullable(); // se asigna al activar/renovar
            $table->timestamp('trial_starts_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();   // 30 días desde la creación
            $table->timestamp('active_from')->nullable();     // fecha de inicio de licencia pagada
            $table->timestamp('active_until')->nullable();    // fecha de fin de licencia pagada
            $table->boolean('is_active')->default(true);      // desactivar manualmente si hiciera falta
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('team_licenses');
    }
};
