<?php

namespace App\Http\Controllers;

use App\Models\LicenseCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LicenseCodeController extends Controller
{
    /**
     * Presets permitidos para generar códigos.
     * clave => [type, duration_unit, duration_value, etiqueta]
     */
    public const PRESETS = [
        'license_1'  => ['license', 'months', 1,  'Licencia · 1 mes'],
        'license_3'  => ['license', 'months', 3,  'Licencia · 3 meses'],
        'license_6'  => ['license', 'months', 6,  'Licencia · 6 meses'],
        'license_12' => ['license', 'months', 12, 'Licencia · 12 meses'],
        'trial_1w'   => ['trial',   'weeks',  1,  'Prueba · 1 semana'],
        'trial_2w'   => ['trial',   'weeks',  2,  'Prueba · 2 semanas'],
    ];

    public function index(Request $request)
    {
        $codes = LicenseCode::with(['redeemedTeam', 'creator'])
            ->latest()
            ->paginate(25);

        return view('admin.license-codes.index', [
            'codes'   => $codes,
            'presets' => self::PRESETS,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'preset'   => ['required', 'string', 'in:' . implode(',', array_keys(self::PRESETS))],
            'label'    => ['nullable', 'string', 'max:255'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        [$type, $unit, $value] = self::PRESETS[$data['preset']];
        $quantity = (int) ($data['quantity'] ?? 1);

        $created = [];
        for ($i = 0; $i < $quantity; $i++) {
            $created[] = LicenseCode::create([
                'code'           => LicenseCode::generateUniqueCode(),
                'type'           => $type,
                'duration_unit'  => $unit,
                'duration_value' => $value,
                'label'          => $data['label'] ?? null,
                'max_uses'       => 1,
                'used_count'     => 0,
                'is_active'      => true,
                'created_by'     => Auth::id(),
            ]);
        }

        $msg = $quantity === 1
            ? "Código generado: {$created[0]->code}"
            : "{$quantity} códigos generados correctamente.";

        return back()->with('success', $msg);
    }

    public function toggle(LicenseCode $licenseCode)
    {
        $licenseCode->is_active = ! $licenseCode->is_active;
        $licenseCode->save();

        return back()->with('success', $licenseCode->is_active
            ? 'Código activado.'
            : 'Código desactivado.');
    }

    public function destroy(LicenseCode $licenseCode)
    {
        $licenseCode->delete();

        return back()->with('success', 'Código eliminado.');
    }
}
