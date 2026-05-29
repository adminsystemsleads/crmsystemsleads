<?php
// app/Http/Controllers/TeamMemberProfileController.php
namespace App\Http\Controllers;

use App\Models\TeamMemberProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamMemberProfileController extends Controller
{
    // (Opcional) Listado de perfiles para admin del team actual
    public function index()
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        // Solo admin del team
        if (! $user->hasTeamRole($team, 'admin')) {
            abort(403);
        }

        $perfiles = TeamMemberProfile::with('user')
            ->where('team_id', $team->id)
            ->orderBy('unidad')
            ->paginate(20);

        return view('team.perfiles-index', compact('perfiles'));
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
