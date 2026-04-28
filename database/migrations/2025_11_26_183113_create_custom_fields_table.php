<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_fields', function (Blueprint $table) {
            $table->id();

            $table->foreignId('team_id')
                ->constrained()
                ->onDelete('cascade');

            // contact | deal
            $table->string('entity_type');

            $table->string('name');      // Nombre visible
            $table->string('slug');      // identificador interno
            $table->string('field_type'); // text, number, date, boolean, select, etc.

            $table->json('options')->nullable(); // para selects, etc.

            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);

            $table->timestamps();

            $table->unique(['team_id', 'entity_type', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_fields');
    }
};
