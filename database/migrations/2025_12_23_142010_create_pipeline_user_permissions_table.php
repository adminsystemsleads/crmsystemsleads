<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pipeline_user_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pipeline_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // Permisos
            $table->boolean('can_view')->default(true);
            $table->boolean('can_edit')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->boolean('can_configure')->default(false);

            $table->timestamps();

            $table->unique(['pipeline_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pipeline_user_permissions');
    }
};
