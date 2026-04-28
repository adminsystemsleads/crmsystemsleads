<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::table('whatsapp_messages', function (Blueprint $table) {
      $table->string('media_id')->nullable()->after('type');
      $table->string('mime_type')->nullable()->after('media_id');
      $table->unsignedBigInteger('file_size')->nullable()->after('mime_type');
      $table->string('filename')->nullable()->after('file_size');
      $table->string('caption')->nullable()->after('filename');
      $table->string('storage_path')->nullable()->after('caption'); // ej: whatsapp/2/....
      $table->string('public_url')->nullable()->after('storage_path'); // ej: /storage/...
    });
  }

  public function down(): void {
    Schema::table('whatsapp_messages', function (Blueprint $table) {
      $table->dropColumn([
        'media_id','mime_type','file_size','filename','caption','storage_path','public_url'
      ]);
    });
  }
};
