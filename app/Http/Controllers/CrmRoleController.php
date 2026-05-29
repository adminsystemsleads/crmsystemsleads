<?php

namespace App\Http\Controllers;

use App\Models\CrmRole;
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

        $role = new CrmRole(['team_id' => $team->id, 'permissions' => []]);
        $groups = CrmPermissions::groups();

        return view('crm-roles.create', compact('role', 'groups'));
    }

    public function store(Request $request)
    {
        [$user, $team] = $this->authorizeAdmin();

        $data = $request->validate([
            'name'          => ['required', 'string', 'max:120', Rule::unique('crm_roles')->where(fn ($q) => $q->where('team_id', $team->id))],
            'description'   => ['nullable', 'string', 'max:255'],
            'permissions'   => ['array'],
            'permissions.*' => ['string', Rule::in(CrmPermissions::allKeys())],
        ]);

        CrmRole::create([
            'team_id'     => $team->id,
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'is_default'  => false,
            'permissions' => $data['permissions'] ?? [],
        ]);

        return redirect()->route('team.crm-roles.index')->with('status', 'Rol creado correctamente.');
    }

    public function edit(CrmRole $role)
    {
        [$user, $team] = $this->authorizeAdmin();
        abort_unless($role->team_id === $team->id, 403);

        $groups = CrmPermissions::groups();
        return view('crm-roles.edit', compact('role', 'groups'));
    }

    public function update(Request $request, CrmRole $role)
    {
        [$user, $team] = $this->authorizeAdmin();
        abort_unless($role->team_id === $team->id, 403);

        $data = $request->validate([
            'name'          => ['required', 'string', 'max:120', Rule::unique('crm_roles')->ignore($role->id)->where(fn ($q) => $q->where('team_id', $team->id))],
            'description'   => ['nullable', 'string', 'max:255'],
            'permissions'   => ['array'],
            'permissions.*' => ['string', Rule::in(CrmPermissions::allKeys())],
        ]);

        // Si es el rol default ("Administrador"), forzamos TODOS los permisos para
        // que nunca quede el sistema sin alguien que pueda administrarlo.
        $permissions = $role->is_default
            ? CrmPermissions::allKeys()
            : ($data['permissions'] ?? []);

        $role->update([
            'name'        => $role->is_default ? $role->name : $data['name'],
            'description' => $data['description'] ?? null,
            'permissions' => $permissions,
        ]);

        return redirect()->route('team.crm-roles.index')->with('status', 'Rol actualizado correctamente.');
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
