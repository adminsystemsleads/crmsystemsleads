<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('subscription_plan_id');

            // IDs en Culqi
            $table->string('culqi_customer_id', 80)->nullable();    // cus_xxx
            $table->string('culqi_card_id', 80)->nullable();        // crd_xxx
            $table->string('culqi_subscription_id', 80)->nullable();// sxn_xxx

            // Datos de la tarjeta para mostrar (último 4)
            $table->string('card_brand', 30)->nullable();           // VISA, MASTERCARD
            $table->string('card_last4', 4)->nullable();

            // Estado
            $table->string('status', 30)->default('pending');
            // pending, active, past_due, canceled, paused, failed
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('subscription_plan_id')->references('id')->on('subscription_plans');
            $table->index(['team_id', 'status']);
            $table->index('culqi_subscription_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_subscriptions');
    }
};
