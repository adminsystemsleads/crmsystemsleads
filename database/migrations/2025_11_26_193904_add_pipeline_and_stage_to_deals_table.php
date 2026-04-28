<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            // Solo si NO están ya creadas
            if (!Schema::hasColumn('deals', 'pipeline_id')) {
                $table->foreignId('pipeline_id')
                    ->nullable()
                    ->after('currency') // ajusta la posición si quieres
                    ->constrained('pipelines')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('deals', 'stage_id')) {
                $table->foreignId('stage_id')
                    ->nullable()
                    ->after('pipeline_id')
                    ->constrained('pipeline_stages')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            if (Schema::hasColumn('deals', 'stage_id')) {
                $table->dropForeign(['stage_id']);
                $table->dropColumn('stage_id');
            }

            if (Schema::hasColumn('deals', 'pipeline_id')) {
                $table->dropForeign(['pipeline_id']);
                $table->dropColumn('pipeline_id');
            }
        });
    }
};
