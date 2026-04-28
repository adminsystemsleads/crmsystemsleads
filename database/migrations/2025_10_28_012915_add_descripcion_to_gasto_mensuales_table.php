<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('gasto_mensuales', function (Blueprint $table) {
        $table->string('descripcion', 255)->nullable()->after('monto_pagar');
    });
}

public function down()
{
    Schema::table('gasto_mensuales', function (Blueprint $table) {
        $table->dropColumn('descripcion');
    });
}
};
