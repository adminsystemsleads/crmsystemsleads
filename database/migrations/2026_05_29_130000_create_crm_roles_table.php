<?php

use App\Models\Team;
use App\Models\CrmRole;
use App\Support\CrmPermissions;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('description')->nullable();
            $table->boolean('is_default')->default(false);  // rol "Administrador" creado por sistema
            $table->json('permissions')->nullable();
            $table->timestamps();

            $table->unique(['team_id', 'name']);
        });

        // Crear el rol "Administrador" por defecto en cada team existente, con todos los permisos.
        // withTrashed(): esta migración corre antes de que se agregue la columna
        // teams.deleted_at, así que evitamos el scope de SoftDeletes del modelo.
        foreach (Team::withTrashed()->get() as $team) {
            CrmRole::firstOrCreate(
                ['team_id' => $team->id, 'name' => 'Administrador'],
                [
                    'description' => 'Acceso total a todas las herramientas del CRM. Rol creado por el sistema.',
                    'is_default'  => true,
                    'permissions' => CrmPermissions::allKeys(),
                ]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_roles');
    }
};
