<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('team_subscription_id')->nullable()->after('user_id');
            $table->string('event_type', 60)->nullable()->after('status'); // charge.creation.succeeded, etc

            $table->foreign('team_subscription_id')
                ->references('id')->on('team_subscriptions')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['team_subscription_id']);
            $table->dropColumn(['team_subscription_id', 'event_type']);
        });
    }
};
