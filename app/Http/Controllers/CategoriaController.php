<?php

// app/Http/Controllers/CategoriaController.php
namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule; 
class CategoriaController extends Controller
{
    public function index()
{
    $teamId = Auth::user()->currentTeam->id;

    // Trae el total de gastos vinculados a cada categoría
    $categorias = \App\Models\Categoria::where('team_id', $teamId)
        ->withCount('gastos') // <-- importante
        ->orderBy('nombre')
        ->paginate(12);

    return view('categorias.index', compact('categorias'));
}


    public function store(Request $request)
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        // (Opcional) solo admin puede crear
        if (! $user->hasTeamRole($team, 'admin')) {
            abort(403);
        }

        // Validar los campos
        $request->validate([
        'nombre' => [
            'required','string','max:100',
            Rule::unique('categorias')->where(fn($q) => $q->where('team_id', $team->id)),
        ],
        'tipo'   => ['required', Rule::in(['INGRESOS','GASTOS FIJOS','GASTOS VARIABLES','OTROS'])],
        ]);

        Categoria::firstOrCreate(
        [
            'team_id' => $team->id,
            'nombre'  => trim($request->nombre),
        ],
        [
            'tipo'    => $request->tipo,
        ]
    );

        return back()->with('success', 'Categoría creada.');
    }

    public function destroy(\App\Models\Categoria $categoria)
{
    $user = Auth::user();
    $team = $user->currentTeam;

    if ($categoria->team_id !== $team->id) {
        abort(403);
    }
    if (! $user->hasTeamRole($team, 'admin')) {
        abort(403);
    }

    // Protección de backend: si hay gastos vinculados, NO eliminar
    $usos = $categoria->gastos()->count();
    if ($usos > 0) {
        return back()->with('error', "No se puede eliminar la categoría '{$categoria->nombre}' porque está vinculada a {$usos} gasto(s). Reasigna o elimina esos registros primero.");
    }

    $categoria->delete();

    return back()->with('success', 'Categoría eliminada.');
}
}
