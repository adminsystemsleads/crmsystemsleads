<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->cascadeOnDelete();

            // 'core'  -> campo base del contacto (name, email, phone, company)
            // 'custom'-> campo personalizado (de contacto o de negociación)
            $table->enum('source', ['core', 'custom'])->default('core');
            $table->string('core_key')->nullable();  // name | email | phone | company
            $table->foreignId('custom_field_id')->nullable()->constrained('custom_fields')->cascadeOnDelete();

            $table->string('label')->nullable();       // etiqueta a mostrar (override)
            $table->string('placeholder')->nullable();
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('form_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_fields');
    }
};
