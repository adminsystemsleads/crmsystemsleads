<?php

namespace App\Http\Controllers;

use App\Models\Pipeline;
use App\Models\PipelineUserPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PipelinePermissionController extends Controller
{
    protected function currentTeam()
    {
        return Auth::user()->currentTeam;
    }

    protected function pipelineForTeam($id): Pipeline
    {
        $team = $this->currentTeam();

        return Pipeline::where('team_id', $team->id)
            ->where('id', $id)
            ->firstOrFail();
    }

    protected function ensureTeamAdmin($team)
{
    $user = Auth::user();

    // 0) Si no hay team o user, fuera
    if (!$team || !$user) {
        abort(403, 'No tienes permisos para configurar este embudo.');
    }

    // 1) Owner siempre
    if ((int)$team->owner_id === (int)$user->id) {
        return;
    }

    // 2) Admin global (si tienes columna users.is_admin)
    if (!empty($user->is_admin)) {
        return;
    }

    // 3) Admin por rol Jetstream (team_user.role)
    // Jetstream normalmente usa hasTeamRole($team, 'admin')
    if (method_exists($user, 'hasTeamRole') && $user->hasTeamRole($team, 'admin')) {
        return;
    }

    // 4) Alternativa por pivot directo (por si no está hasTeamRole)
    $isAdminPivot = $team->users()
        ->where('users.id', $user->id)
        ->wherePivot('role', 'admin')
        ->exists();

    if ($isAdminPivot) {
        return;
    }

    abort(403, 'No tienes permisos para configurar este embudo.');
}


    public function edit(Pipeline $pipeline)
    {
        $pipeline = $this->pipelineForTeam($pipeline->id);
        $team     = $this->currentTeam();

        $this->ensureTeamAdmin($team);

        // Todos los usuarios del team (incluye admin/owner)
        $teamMembers = $team->users()->orderBy('name')->get();

        // Permisos existentes indexados por user_id
        $permissions = $pipeline->permissions()
            ->get()
            ->keyBy('user_id');

        return view('pipelines.permissions', compact(
            'pipeline',
            'team',
            'teamMembers',
            'permissions'
        ));
    }

    public function update(Request $request, Pipeline $pipeline)
{
    $pipeline = $this->pipelineForTeam($pipeline->id);
    $team     = $this->currentTeam();

    $this->ensureTeamAdmin($team);

    // ✅ Checkbox envía "on" (string) o no envía nada
    $data = $request->validate([
        'permissions' => 'array',

        // Usar accepted para checkbox
        'permissions.*.can_view'      => 'sometimes|accepted',
        'permissions.*.can_edit'      => 'sometimes|accepted',
        'permissions.*.can_delete'    => 'sometimes|accepted',
        'permissions.*.can_configure' => 'sometimes|accepted',
    ]);

    $permissionsInput = $data['permissions'] ?? [];

    // ✅ Ojo: usa get() o foreach($team->users) cargado previamente
    foreach ($team->users()->get() as $user) {
        $row = $permissionsInput[$user->id] ?? null;

        // Owner siempre tiene TODO (no lo tocamos)
        if ((int)$team->owner_id === (int)$user->id) {
            continue;
        }

        // (Opcional) si tienes admin en pivot role y quieres que admin sea intocable:
        // if ($team->users()->where('users.id',$user->id)->wherePivot('role','admin')->exists()) continue;

        if (!$row) {
            // Si no vino nada para ese user, borramos el registro
            PipelineUserPermission::where('pipeline_id', $pipeline->id)
                ->where('user_id', $user->id)
                ->delete();
            continue;
        }

        PipelineUserPermission::updateOrCreate(
            [
                'pipeline_id' => $pipeline->id,
                'user_id'     => $user->id,
            ],
            [
                // ✅ Convertimos a true/false real
                'can_view'      => !empty($row['can_view']),
                'can_edit'      => !empty($row['can_edit']),
                'can_delete'    => !empty($row['can_delete']),
                'can_configure' => !empty($row['can_configure']),
            ]
        );
    }

    return back()->with('status', 'Permisos del embudo actualizados correctamente.');
}

}
