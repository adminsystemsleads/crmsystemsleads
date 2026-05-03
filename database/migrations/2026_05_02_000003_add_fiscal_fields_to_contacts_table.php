<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->string('tipo_doc', 2)->nullable()->after('email');   // '1'=DNI,'6'=RUC,'4'=CE
            $table->string('num_doc', 15)->nullable()->after('tipo_doc');
            $table->string('razon_social', 250)->nullable()->after('num_doc');
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn(['tipo_doc', 'num_doc', 'razon_social']);
        });
    }
};
