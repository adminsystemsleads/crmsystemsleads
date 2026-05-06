<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 50)->unique();          // basic, pro, enterprise
            $table->string('name', 150);                    // "QipuCRM Básico"
            $table->string('description', 500)->nullable();
            $table->unsignedBigInteger('amount_cents');     // 4990 = S/ 49.90
            $table->string('currency', 3)->default('PEN');
            $table->string('interval', 20)->default('meses'); // meses, años (formato Culqi)
            $table->unsignedSmallInteger('interval_count')->default(1);
            $table->unsignedSmallInteger('trial_days')->default(0);
            $table->string('culqi_plan_id', 80)->nullable();// pln_xxx (lo asignamos al sincronizar)
            $table->boolean('is_active')->default(true);
            $table->json('features')->nullable();           // ["WhatsApp","IA","Facturación"]
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
