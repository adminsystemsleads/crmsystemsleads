<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deals', function (Blueprint $table) {
            $table->id();

            // Multi-tenant
            $table->foreignId('team_id')
                ->constrained()
                ->onDelete('cascade');

            // Dueño de la negociación
            $table->foreignId('owner_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Contacto principal asociado
            $table->foreignId('contact_id')
                ->nullable()
                ->constrained('contacts')
                ->nullOnDelete();

            $table->string('title'); // nombre de la negociación
            $table->decimal('amount', 15, 2)->nullable();
            $table->string('currency', 3)->default('PEN');

            $table->string('stage')->default('nuevo');  // etapa del pipeline
            $table->string('pipeline')->nullable();     // por si luego tienes varios pipelines

            $table->enum('status', ['open', 'won', 'lost'])
                ->default('open');

            $table->date('close_date')->nullable();
            $table->text('description')->nullable();

            $table->timestamps();

            $table->index(['team_id', 'owner_id', 'contact_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};
