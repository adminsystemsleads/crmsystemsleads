<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Códigos de licencia generados por el Super Administrador.
 *
 *  - type = 'license'  -> duración en meses  (1, 3, 6, 12)  => "Licencia activada"
 *  - type = 'trial'    -> duración en semanas (1, 2)         => "Modo de Prueba activo"
 *
 * Cada código se canjea una vez por defecto (max_uses = 1).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('license_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();

            $table->string('type');           // 'license' | 'trial'
            $table->string('duration_unit');  // 'months'  | 'weeks'
            $table->unsignedInteger('duration_value');

            $table->string('label')->nullable();   // nota / cliente destino
            $table->unsignedInteger('max_uses')->default(1);
            $table->unsignedInteger('used_count')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamp('redeemed_at')->nullable();
            $table->foreignId('redeemed_by_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->foreignId('redeemed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_codes');
    }
};
