<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamModulesController extends Controller
{
    private array $allModules = [
        ['key' => 'crm',              'label' => 'CRM / Pipelines',          'desc' => 'Gestión de negocios y embudos de venta', 'admin_only' => false],
        ['key' => 'whatsapp_inbox',   'label' => 'WhatsApp',                 'desc' => 'Bandeja de entrada de conversaciones',   'admin_only' => false],
        ['key' => 'whatsapp_cuentas', 'label' => 'WhatsApp Cuentas (Admin)', 'desc' => 'Gestión de cuentas y números conectados', 'admin_only' => true],
        ['key' => 'finanzas',         'label' => 'Mis Finanzas',             'desc' => 'Resumen financiero de la unidad',         'admin_only' => false],
        ['key' => 'pagos',            'label' => 'Pagos',                    'desc' => 'Historial y registro de pagos',           'admin_only' => false],
        ['key' => 'transparencia_ia', 'label' => 'Transparencia IA',         'desc' => 'Auditoría de acciones automatizadas por IA', 'admin_only' => false],
        ['key' => 'perfil_unidad',    'label' => 'Mi Perfil de Unidad',      'desc' => 'Datos de la unidad habitacional',         'admin_only' => false],
        ['key' => 'gastos',           'label' => 'Lista de Gastos',          'desc' => 'Visualización de gastos mensuales',       'admin_only' => true],
        ['key' => 'gastos_import',    'label' => 'Importar Reporte',         'desc' => 'Carga masiva de reportes de gastos',      'admin_only' => true],
        ['key' => 'perfiles',         'label' => 'Perfiles (Admin)',         'desc' => 'Gestión de perfiles de miembros',         'admin_only' => true],
        ['key' => 'categorias',       'label' => 'Categorías de Pago',       'desc' => 'Administración de tipos de pago',         'admin_only' => true],
    ];

    public function edit(Team $team)
    {
        abort_unless(
            Auth::user()->ownsTeam($team) ||
            Auth::user()->hasTeamRole($team, 'admin'),
            403
        );

        $modules = $this->allModules;

        return view('team.modules', compact('team', 'modules'));
    }

    public function update(Request $request, Team $team)
    {
        abort_unless(
            Auth::user()->ownsTeam($team) ||
            Auth::user()->hasTeamRole($team, 'admin'),
            403
        );

        $keys = array_column($this->allModules, 'key');
        $enabled = [];
        foreach ($keys as $key) {
            $enabled[$key] = $request->boolean($key);
        }

        $settings = $team->settings ?? [];
        $settings['modules'] = $enabled;
        $team->update(['settings' => $settings]);

        return back()->with('status', 'modules-updated');
    }
}
