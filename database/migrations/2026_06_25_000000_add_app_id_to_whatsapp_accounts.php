<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('whatsapp_accounts', 'app_id')) {
                $table->string('app_id')->nullable()->after('business_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_accounts', function (Blueprint $table) {
            if (Schema::hasColumn('whatsapp_accounts', 'app_id')) {
                $table->dropColumn('app_id');
            }
        });
    }
};
