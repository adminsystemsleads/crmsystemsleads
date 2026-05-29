<?php
// app/Http/Controllers/TeamMemberProfileController.php
namespace App\Http\Controllers;

use App\Models\CrmRole;
use App\Models\TeamMemberProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamMemberProfileController extends Controller
{
    // Listado de usuarios del CRM (todos los miembros del team actual con su perfil + rol)
    public function index()
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        // Solo admin del team
        if (! $user->hasTeamRole($team, 'admin')) {
            abort(403);
        }

        // allUsers() de Jetstream devuelve owner + members del pivot team_user
        $members = $team->allUsers();

        // Construir colección unificada: para cada user, su profile + crmRole
        $profilesByUser = TeamMemberProfile::with('crmRole')
            ->where('team_id', $team->id)
            ->get()
            ->keyBy('user_id');

        $rows = $members->map(function ($member) use ($team, $profilesByUser) {
            $profile = $profilesByUser->get($member->id);
            $isOwner = ((int) $team->user_id === (int) $member->id);
            $isAdmin = $isOwner || $member->hasTeamRole($team, 'admin');

            return (object) [
                'user'        => $member,
                'profile'     => $profile,
                'role_name'   => $profile?->crmRole?->name
                                 ?: ($isAdmin ? 'Administrador (por defecto)' : 'Sin rol asignado'),
                'is_owner'    => $isOwner,
                'is_admin'    => $isAdmin,
            ];
        });

        $onlyOneUser = $members->count() === 1;

        return view('team.perfiles-index', compact('rows', 'onlyOneUser'));
    }

    // Form para que un admin edite el perfil y rol de OTRO miembro del team
    public function editMember(User $member)
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        if (! $user->hasTeamRole($team, 'admin')) {
            abort(403);
        }

        // Validar que el member pertenece al team actual
        $allUsers = $team->allUsers();
        if (! $allUsers->contains('id', $member->id)) {
            abort(404, 'El usuario no pertenece a este equipo.');
        }

        $profile = TeamMemberProfile::with('crmRole')->firstOrCreate(
            ['team_id' => $team->id, 'user_id' => $member->id],
            ['correo' => $member->email]
        );

        $roles = CrmRole::where('team_id', $team->id)->orderByDesc('is_default')->orderBy('name')->get();
        $onlyOneUser = $allUsers->count() === 1;
        $isEditingSelf = ((int) $user->id === (int) $member->id);
        $isOwner = ((int) $team->user_id === (int) $member->id);

        return view('team.edit-member', compact(
            'member', 'profile', 'roles', 'onlyOneUser', 'isEditingSelf', 'isOwner'
        ));
    }

    public function updateMember(Request $request, User $member)
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        if (! $user->hasTeamRole($team, 'admin')) {
            abort(403);
        }

        $allUsers = $team->allUsers();
        if (! $allUsers->contains('id', $member->id)) {
            abort(404, 'El usuario no pertenece a este equipo.');
        }

        $data = $request->validate([
            'nombre'      => 'required|string|max:255',
            'correo'      => 'nullable|email|max:120',
            'telefono'    => 'nullable|string|max:50',
            'notas'       => 'nullable|string|max:2000',
            'crm_role_id' => 'nullable|exists:crm_roles,id',
        ]);

        // Regla anti-bloqueo: si solo hay 1 usuario en el team, no permitir cambiar el rol
        // (el único usuario es necesariamente el admin; cambiarlo dejaría sin admin al equipo)
        $onlyOneUser = $allUsers->count() === 1;

        // Tampoco permitir que el admin se cambie su propio rol (a sí mismo)
        $isEditingSelf = ((int) $user->id === (int) $member->id);

        $member->forceFill(['name' => $data['nombre']])->save();

        $profile = TeamMemberProfile::firstOrCreate(
            ['team_id' => $team->id, 'user_id' => $member->id]
        );

        $payload = [
            'correo'   => $data['correo']   ?? null,
            'telefono' => $data['telefono'] ?? null,
            'notas'    => $data['notas']    ?? null,
        ];

        // Solo aceptamos crm_role_id si NO es el único usuario Y NO es editar su propio rol
        if (! $onlyOneUser && ! $isEditingSelf) {
            // Validar que el rol pertenece al team actual (defensa adicional)
            if (! empty($data['crm_role_id'])) {
                $role = CrmRole::find($data['crm_role_id']);
                if ($role && (int) $role->team_id === (int) $team->id) {
                    $payload['crm_role_id'] = $role->id;
                }
            } else {
                $payload['crm_role_id'] = null;
            }
        }

        $profile->fill($payload)->save();

        return redirect()->route('team.perfiles.index')
            ->with('success', "Perfil de {$member->name} actualizado correctamente.");
    }

    // Form para el propio perfil del usuario en el team actual
    public function edit()
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        $perfil = TeamMemberProfile::with('crmRole')->firstOrCreate(
            ['team_id' => $team->id, 'user_id' => $user->id],
            [
                'perfil' => null, 'unidad' => null, 'correo' => $user->email,
                'telefono' => null, 'notas' => null
            ]
        );

        // Si el usuario no tiene rol asignado pero es owner/admin del team,
        // mostramos "Administrador" como rol implícito (sin guardar todavía).
        $isOwner = ((int) $team->user_id === (int) $user->id);
        $isAdmin = $isOwner || $user->hasTeamRole($team, 'admin');

        $rolDisplay = $perfil->crmRole?->name
            ?: ($isAdmin ? 'Administrador (rol por defecto del sistema)' : 'Sin rol asignado');

        return view('team.mi-perfil-unidad', compact('perfil', 'user', 'rolDisplay'));
    }

    // Guardar cambios del propio perfil
    public function update(Request $request)
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        $data = $request->validate([
            'nombre'   => 'required|string|max:255',
            'correo'   => 'nullable|email|max:120',
            'telefono' => 'nullable|string|max:50',
            'notas'    => 'nullable|string|max:2000',
        ]);

        // Nombre se persiste en la tabla users (no en el perfil)
        $user->forceFill(['name' => $data['nombre']])->save();

        $perfil = TeamMemberProfile::firstOrCreate(
            ['team_id' => $team->id, 'user_id' => $user->id]
        );

        // OJO: NO aceptamos crm_role_id ni perfil ni unidad desde esta vista
        $perfil->fill([
            'correo'   => $data['correo']   ?? null,
            'telefono' => $data['telefono'] ?? null,
            'notas'    => $data['notas']    ?? null,
        ])->save();

        return back()->with('success', 'Perfil actualizado correctamente.');
    }
}
