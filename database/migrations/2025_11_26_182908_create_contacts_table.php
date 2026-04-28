<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();

            // Multi-tenant
            $table->foreignId('team_id')
                ->constrained()
                ->onDelete('cascade');

            // Dueño del contacto (usuario del team)
            $table->foreignId('owner_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('name'); // nombre completo visible
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('company')->nullable();
            $table->string('position')->nullable();

            $table->string('status')->default('nuevo'); // nuevo, activo, inactivo, etc.
            $table->string('source')->nullable();       // origen del lead

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['team_id', 'owner_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
