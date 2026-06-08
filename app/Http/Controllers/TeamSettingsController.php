<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TeamSettingsController extends Controller
{
    /**
     * Actualiza la zona horaria de la cuenta (equipo).
     */
    public function updateTimezone(Request $request, Team $team)
    {
        Gate::authorize('update', $team);

        $data = $request->validate([
            'timezone' => ['required', 'string', 'timezone'],
        ]);

        $team->forceFill(['timezone' => $data['timezone']])->save();

        return back()
            ->with('success', 'Zona horaria actualizada.')
            ->with('flash', ['banner' => 'Zona horaria actualizada.', 'bannerStyle' => 'success']);
    }
}
