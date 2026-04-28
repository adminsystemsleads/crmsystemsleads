<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GastoMensual;
use App\Models\Categoria;
use App\Models\TeamMemberProfile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // <-- importa
class GastoController extends Controller
{
    use AuthorizesRequests; // <-- añade esto
    public function index(Request $request)
{
    $user = Auth::user();
    $team = $user->currentTeam;

    $categorias = Categoria::where('team_id', $team->id)
        ->orderBy('nombre')->get();

    $perfiles = TeamMemberProfile::where('team_id', $team->id)
        ->orderBy('unidad')->get();

    $query = GastoMensual::where('team_id', $team->id)
        ->with(['categoria', 'memberProfile'])
        ->orderByDesc('año')      // columna real con tilde
        ->orderByDesc('mes');

    if ($request->filled('mes')) {
        $query->where('mes', $request->mes);
    }

    if ($request->filled('anio')) {
        // ¡OJO! en BD es "año"
        $query->where('año', $request->anio);
    }

    if ($request->filled('categoria_id')) {
        $query->where('categoria_id', $request->categoria_id);
    }

    // Filtro de perfil: acepta 'general' (sin unidad) o un ID
    if ($request->filled('perfil')) {
        if ($request->perfil === 'general') {
            $query->whereNull('team_member_profile_id');
        } else {
            $query->where('team_member_profile_id', $request->perfil);
        }
    }

    $gastos = $query->paginate(20)->withQueryString();

    return view('gastos.index', compact('gastos', 'categorias', 'perfiles', 'request'));
}

    public function edit(GastoMensual $gasto)
    {
        $this->authorize('update', $gasto);
        $categorias = Categoria::where('team_id', $gasto->team_id)->get();
        return view('gastos.edit', compact('gasto', 'categorias'));
    }

public function update(Request $request, GastoMensual $gasto)
{
    $user = Auth::user();
    $team = $user->currentTeam;
    abort_unless($gasto->team_id === $team->id, 403);

    $validated = $request->validate([
        'team_member_profile_id' => ['nullable','exists:team_member_profiles,id'],
        'categoria_id'           => ['required','exists:categorias,id'],
        'mes'                    => ['required','integer','between:1,12'],
        'anio'                   => ['required','integer','between:2000,2100'],
        'monto_pagar'            => ['required','numeric','min:0'],
        'codigopago'             => ['nullable','string','max:255'],
        'descripcion'            => ['nullable','string','max:1000'],
        'dia_pago'               => ['nullable','date'],
        'link_vaucher'           => ['nullable','url','max:500'],
        // importante: permitir 0/1
        'pago_verificado'        => ['required','in:0,1'],
    ]);

    // Convierte a boolean
    $validated['pago_verificado'] = $request->boolean('pago_verificado');

    $gasto->update($validated);

    return redirect()->route('gastos.index')
        ->with('success', 'Gasto actualizado correctamente.');
}


    public function destroy(GastoMensual $gasto)
    {
        // Si no tienes Policy, reemplaza authorize por un chequeo manual:
        abort_unless($gasto->team_id === Auth::user()->currentTeam->id, 403, 'No autorizado.');

        $gasto->delete();
        return back()->with('success', 'Gasto eliminado correctamente.');
    }

    public function ask(Request $request)
{
    $request->validate([
        'q' => 'required|string|max:1000',
    ]);

    $team = Auth::user()->currentTeam;

    /** @var AiSqlAnswerService $svc */
    $svc = app(AiSqlAnswerService::class);

    [$sql, $rows, $answer, $columns] = $svc->answerQuestionForTeam([
        'teamId'          => $team->id,
        'naturalQuestion' => $request->q,
    ]);

    return view('transparencia_ia.index', [
        'q'       => $request->q,
        'sql'     => $sql,
        'rows'    => $rows,
        'answer'  => $answer,
        'columns' => $columns,
    ]);
}


}
