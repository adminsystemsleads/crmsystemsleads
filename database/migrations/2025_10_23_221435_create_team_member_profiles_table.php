<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_team_member_profiles_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('team_member_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Datos del perfil
            $table->enum('perfil', ['propietario', 'residente'])->nullable(); // Perfil(Propietario/Residente)
            $table->string('unidad')->nullable();      // p.ej. "Torre A - 502"
            $table->string('correo')->nullable();      // puede diferir del email de login
            $table->string('telefono')->nullable();
            $table->text('notas')->nullable();

            $table->timestamps();

            $table->unique(['team_id', 'user_id']); // un perfil por miembro en cada team
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_member_profiles');
    }
};
