<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pipeline_stages', function (Blueprint $table) {
            // Hex color, ej: #6366f1
            $table->string('color', 9)->default('#6366f1')->after('probability');
        });
    }

    public function down(): void
    {
        Schema::table('pipeline_stages', function (Blueprint $table) {
            $table->dropColumn('color');
        });
    }
};
