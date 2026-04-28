<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            // guarda el número/wa_id del cliente para encontrar su deal abierto
            $table->string('wa_id', 50)->nullable()->index()->after('contact_id');
        });
    }

    public function down(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->dropIndex(['wa_id']);
            $table->dropColumn('wa_id');
        });
    }
};
