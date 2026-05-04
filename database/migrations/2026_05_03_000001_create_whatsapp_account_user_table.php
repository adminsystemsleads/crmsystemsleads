<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_account_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('whatsapp_account_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('whatsapp_account_id')->references('id')->on('whatsapp_accounts')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['whatsapp_account_id', 'user_id']);
        });

        Schema::table('whatsapp_accounts', function (Blueprint $table) {
            $table->unsignedBigInteger('last_assigned_user_id')->nullable()->after('pipeline_id');
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_accounts', function (Blueprint $table) {
            $table->dropColumn('last_assigned_user_id');
        });
        Schema::dropIfExists('whatsapp_account_user');
    }
};
