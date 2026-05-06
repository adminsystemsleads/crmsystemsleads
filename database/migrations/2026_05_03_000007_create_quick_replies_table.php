<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quick_replies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('shortcut', 50)->nullable(); // ej: saludo, precio
            $table->string('title', 150);
            $table->text('content');
            $table->boolean('is_team_wide')->default(true); // visible a todo el team
            $table->unsignedInteger('times_used')->default(0);
            $table->timestamps();

            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['team_id', 'shortcut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quick_replies');
    }
};
