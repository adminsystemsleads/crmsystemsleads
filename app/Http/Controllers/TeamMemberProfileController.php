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

        $perfil = TeamMemberProfile::firstOrCreate(
            ['team_id' => $team->id, 'user_id' => $user->id],
            [
                // valores por defecto
                'perfil' => null, 'unidad' => null, 'correo' => $user->email,
                'telefono' => null, 'notas' => null
            ]
        );

        return view('team.mi-perfil-unidad', compact('perfil'));
    }

    // Guardar cambios del propio perfil
    public function update(Request $request)
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        $data = $request->validate([
            'perfil'   => 'nullable|in:propietario,residente',
            'unidad'   => 'nullable|string|max:120',
            'correo'   => 'nullable|email|max:120',
            'telefono' => 'nullable|string|max:50',
            'notas'    => 'nullable|string|max:2000',
        ]);

        $perfil = TeamMemberProfile::firstOrCreate(
            ['team_id' => $team->id, 'user_id' => $user->id]
        );

        $perfil->fill($data)->save();

        return back()->with('success', 'Perfil actualizado correctamente.');
    }
}
