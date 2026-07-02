<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('form_submissions', function (Blueprint $table) {
            $table->id();
            // Si el formulario se elimina, conservamos el lead (nullOnDelete + snapshot del nombre).
            $table->foreignId('form_id')->nullable()->constrained('forms')->nullOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->foreignId('deal_id')->nullable()->constrained('deals')->nullOnDelete();

            $table->string('form_name')->nullable();   // snapshot por si se borra el form
            $table->json('payload')->nullable();        // datos crudos enviados
            $table->string('ip', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index(['team_id', 'form_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
    }
};
