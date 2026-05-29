<?php

namespace App\Http\Controllers;

use App\Models\CrmRole;
use App\Models\Pipeline;
use App\Support\CrmPermissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CrmRoleController extends Controller
{
    /**
     * Solo admin del team actual puede gestionar roles. Aborta 403 si no.
     */
    protected function authorizeAdmin(): array
    {
        $user = Auth::user();
        $team = $user->currentTeam;
        abort_unless($team && $user->hasTeamRole($team, 'admin'), 403);
        return [$user, $team];
    }

    public function index()
    {
        [$user, $team] = $this->authorizeAdmin();

        $roles = CrmRole::where('team_id', $team->id)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        return view('crm-roles.index', compact('roles'));
    }

    public function create()
    {
        [$user, $team] = $this->authorizeAdmin();

        $role = new CrmRole([
            'team_id'              => $team->id,
            'permissions'          => [],
            'allowed_pipeline_ids' => [],
        ]);
        $groups = CrmPermissions::groups();
        $teamPipelines = $this->teamPipelines($team->id);

        return view('crm-roles.create', compact('role', 'groups', 'teamPipelines'));
    }

    public function store(Request $request)
    {
        [$user, $team] = $this->authorizeAdmin();

        $data = $this->validatePayload($request, $team, null);

        CrmRole::create([
            'team_id'              => $team->id,
            'name'                 => $data['name'],
            'description'          => $data['description'] ?? null,
            'is_default'           => false,
            'permissions'          => $data['permissions'] ?? [],
            'allowed_pipeline_ids' => $this->resolveAllowedPipelineIds($data),
        ]);

        return redirect()->route('team.crm-roles.index')->with('status', 'Rol creado correctamente.');
    }

    public function edit(CrmRole $role)
    {
        [$user, $team] = $this->authorizeAdmin();
        abort_unless($role->team_id === $team->id, 403);

        $groups = CrmPermissions::groups();
        $teamPipelines = $this->teamPipelines($team->id);

        return view('crm-roles.edit', compact('role', 'groups', 'teamPipelines'));
    }

    public function update(Request $request, CrmRole $role)
    {
        [$user, $team] = $this->authorizeAdmin();
        abort_unless($role->team_id === $team->id, 403);

        $data = $this->validatePayload($request, $team, $role);

        // Si es el rol default ("Administrador"), forzamos TODOS los permisos para
        // que nunca quede el sistema sin alguien que pueda administrarlo.
        $permissions = $role->is_default
            ? CrmPermissions::allKeys()
            : ($data['permissions'] ?? []);

        $role->update([
            'name'                 => $role->is_default ? $role->name : $data['name'],
            'description'          => $data['description'] ?? null,
            'permissions'          => $permissions,
            'allowed_pipeline_ids' => $role->is_default ? null : $this->resolveAllowedPipelineIds($data),
        ]);

        return redirect()->route('team.crm-roles.index')->with('status', 'Rol actualizado correctamente.');
    }

    /**
     * Reglas de validación compartidas para store/update.
     * $existing = el rol que se está editando (null si es creación).
     */
    protected function validatePayload(Request $request, $team, ?CrmRole $existing): array
    {
        $nameRule = Rule::unique('crm_roles')->where(fn ($q) => $q->where('team_id', $team->id));
        if ($existing) $nameRule = $nameRule->ignore($existing->id);

        return $request->validate([
            'name'                   => ['required', 'string', 'max:120', $nameRule],
            'description'            => ['nullable', 'string', 'max:255'],
            'permissions'            => ['array'],
            'permissions.*'          => ['string', Rule::in(CrmPermissions::allKeys())],
            'allowed_pipeline_ids'   => ['array'],
            'allowed_pipeline_ids.*' => [
                'integer',
                Rule::exists('pipelines', 'id')->where(fn ($q) => $q->where('team_id', $team->id)),
            ],
        ]);
    }

    /**
     * Devuelve los IDs de embudos a guardar según el toggle pipelines.view_all:
     * - Si view_all está activado → null (sin restricción, ve todos)
     * - Si view_all está apagado  → la lista marcada (puede ser vacía = no ve ninguno)
     */
    protected function resolveAllowedPipelineIds(array $data): ?array
    {
        $perms = (array) ($data['permissions'] ?? []);
        if (in_array('pipelines.view_all', $perms, true)) {
            return null;
        }
        return array_values(array_map('intval', (array) ($data['allowed_pipeline_ids'] ?? [])));
    }

    /**
     * Embudos activos del team (para el selector granular).
     */
    protected function teamPipelines(int $teamId)
    {
        return Pipeline::where('team_id', $teamId)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'is_active']);
    }

    public function destroy(CrmRole $role)
    {
        [$user, $team] = $this->authorizeAdmin();
        abort_unless($role->team_id === $team->id, 403);

        if ($role->is_default) {
            return back()->withErrors(['default' => 'No se puede eliminar el rol Administrador.']);
        }

        $role->delete();
        return redirect()->route('team.crm-roles.index')->with('status', 'Rol eliminado.');
    }
}
