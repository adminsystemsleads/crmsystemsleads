<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\GastoMensual;

class FinanzasController extends Controller
{
    public function index(Request $request)
{
    $user = Auth::user();

    // Defaults: mes/año actuales si no vienen en la URL
    $mes  = (int) ($request->input('mes')  ?: now()->month);
    $anio = (int) ($request->input('anio') ?: now()->year);

    $gastos = GastoMensual::with('categoria')
        ->where('user_id', $user->id)
        ->where('año', $anio)
        ->where('mes', $mes)
        ->where('pago_verificado', 1)
        ->orderByDesc('dia_pago')
        ->get();

    $total = $gastos->sum('monto_pagar');

    return view('finanzas.index', compact('gastos', 'mes', 'anio', 'total'));
}
}
