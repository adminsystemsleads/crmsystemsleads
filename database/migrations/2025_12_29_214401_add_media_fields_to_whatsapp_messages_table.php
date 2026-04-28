<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('whatsapp_messages', function (Blueprint $table) {

            if (!Schema::hasColumn('whatsapp_messages', 'media_id')) {
                $table->string('media_id')->nullable()->after('type');
            }

            if (!Schema::hasColumn('whatsapp_messages', 'mime_type')) {
                $table->string('mime_type')->nullable()->after('media_id');
            }

            if (!Schema::hasColumn('whatsapp_messages', 'file_size')) {
                $table->unsignedBigInteger('file_size')->nullable()->after('mime_type');
            }

            if (!Schema::hasColumn('whatsapp_messages', 'filename')) {
                $table->string('filename')->nullable()->after('file_size');
            }

            if (!Schema::hasColumn('whatsapp_messages', 'caption')) {
                $table->string('caption')->nullable()->after('filename');
            }

            if (!Schema::hasColumn('whatsapp_messages', 'storage_path')) {
                $table->string('storage_path')->nullable()->after('caption');
            }

            if (!Schema::hasColumn('whatsapp_messages', 'public_url')) {
                $table->string('public_url')->nullable()->after('storage_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_messages', function (Blueprint $table) {
            $cols = ['media_id','mime_type','file_size','filename','caption','storage_path','public_url'];

            foreach ($cols as $c) {
                if (Schema::hasColumn('whatsapp_messages', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
