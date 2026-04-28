<?php

// database/migrations/xxxx_xx_xx_create_whatsapp_accounts_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('whatsapp_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->index();

            $table->string('name');
            $table->string('waba_id')->nullable();
            $table->string('phone_number_id')->unique(); // clave para mapear inbound
            $table->string('display_phone_number')->nullable();

            $table->text('access_token'); // CAST encrypted en model
            $table->unsignedBigInteger('pipeline_id')->nullable()->index();
            $table->unsignedBigInteger('default_stage_id')->nullable()->index();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('team_id')->references('id')->on('teams')->cascadeOnDelete();
            $table->foreign('pipeline_id')->references('id')->on('pipelines')->nullOnDelete();
            $table->foreign('default_stage_id')->references('id')->on('pipeline_stages')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_accounts');
    }
};
