<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('whatsapp_accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('whatsapp_accounts', 'verify_token')) {
                $table->string('verify_token', 255)->nullable()->after('access_token');
            }

            if (!Schema::hasColumn('whatsapp_accounts', 'business_id')) {
                $table->string('business_id', 255)->nullable()->after('waba_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_accounts', function (Blueprint $table) {
            if (Schema::hasColumn('whatsapp_accounts', 'verify_token')) {
                $table->dropColumn('verify_token');
            }

            if (Schema::hasColumn('whatsapp_accounts', 'business_id')) {
                $table->dropColumn('business_id');
            }
        });
    }
};
