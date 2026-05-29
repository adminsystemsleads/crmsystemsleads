<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_roles', function (Blueprint $table) {
            // IDs de embudos específicos que este rol puede ver, cuando pipelines.view_all está apagado.
            // Si está vacío y view_all está apagado, el rol no ve ningún embudo.
            $table->json('allowed_pipeline_ids')->nullable()->after('permissions');
        });
    }

    public function down(): void
    {
        Schema::table('crm_roles', function (Blueprint $table) {
            $table->dropColumn('allowed_pipeline_ids');
        });
    }
};
