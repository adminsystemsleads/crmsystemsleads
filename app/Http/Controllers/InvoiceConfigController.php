<?php

namespace App\Http\Controllers;

use App\Models\InvoiceConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceConfigController extends Controller
{
    private function teamConfig()
    {
        $team = Auth::user()->currentTeam;

        return [
            'team'   => $team,
            'config' => InvoiceConfig::firstOrNew(['team_id' => $team->id]),
        ];
    }

    public function edit()
    {
        ['team' => $team, 'config' => $config] = $this->teamConfig();

        return view('invoices.config', compact('team', 'config'));
    }

    public function update(Request $request)
    {
        ['team' => $team, 'config' => $config] = $this->teamConfig();

        $data = $request->validate([
            'ruc'             => 'required|string|size:11',
            'razon_social'    => 'required|string|max:250',
            'nombre_comercial'=> 'nullable|string|max:250',
            'ubigeo'          => 'required|string|size:6',
            'departamento'    => 'required|string|max:100',
            'provincia'       => 'required|string|max:100',
            'distrito'        => 'required|string|max:100',
            'urbanizacion'    => 'nullable|string|max:100',
            'direccion'       => 'required|string|max:250',
            'cod_pais'        => 'required|string|size:2',
            'sol_user'        => 'nullable|string|max:50',
            'sol_password'    => 'nullable|string|max:50',
            'certificate_pem' => 'nullable|string',
            'ambiente'        => 'required|in:beta,produccion',
            'serie_factura'   => 'required|string|size:4',
            'serie_boleta'    => 'required|string|size:4',
            'test_mode'       => 'nullable|boolean',
        ]);

        $data['test_mode'] = $request->boolean('test_mode');

        $config->fill(['team_id' => $team->id] + $data);
        $config->save();

        return back()->with('success', 'Configuración de facturación guardada.');
    }
}
