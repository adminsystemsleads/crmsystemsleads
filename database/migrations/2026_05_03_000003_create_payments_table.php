<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('provider', 30)->default('culqi'); // culqi, stripe, etc
            $table->string('charge_id', 100)->nullable();      // id del cargo en Culqi
            $table->string('source_id', 100)->nullable();      // token tarjeta usado
            $table->unsignedBigInteger('amount_cents');        // 4990 = S/ 49.90
            $table->string('currency', 3)->default('PEN');
            $table->string('status', 30)->default('pending');  // pending, paid, failed, refunded
            $table->unsignedSmallInteger('months')->default(1);
            $table->string('email', 255)->nullable();
            $table->string('description', 250)->nullable();
            $table->json('response')->nullable();              // respuesta cruda de Culqi
            $table->string('error_message', 500)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['team_id', 'status']);
            $table->index('charge_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
