<?php

namespace App\Http\Controllers;

use App\Models\LicenseCode;
use App\Models\Team;
use App\Models\TeamLicense;
use App\Services\TeamLicenseManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LicenseCodeController extends Controller
{
    /**
     * Presets permitidos para generar códigos.
     * clave => [type, duration_unit, duration_value, etiqueta]
     */
    public const PRESETS = [
        'license_1'   => ['license',  'months', 1,  'Licencia · 1 mes'],
        'license_3'   => ['license',  'months', 3,  'Licencia · 3 meses'],
        'license_6'   => ['license',  'months', 6,  'Licencia · 6 meses'],
        'license_12'  => ['license',  'months', 12, 'Licencia · 12 meses'],
        'trial_1w'    => ['trial',    'weeks',  1,  'Prueba · 1 semana'],
        'trial_2w'    => ['trial',    'weeks',  2,  'Prueba · 2 semanas'],
        'trial_3w'    => ['trial',    'weeks',  3,  'Prueba · 3 semanas'],
        'trial_4w'    => ['trial',    'weeks',  4,  'Prueba · 4 semanas'],
        'prorroga_7d' => ['prorroga', 'days',   7,  'Prórroga · 7 días'],
    ];

    public function index(Request $request)
    {
        $codes = LicenseCode::with(['redeemedTeam', 'creator'])
            ->latest()
            ->paginate(25);

        // Cuentas con prórroga: separadas en vigentes y vencidas (cuenta bloqueada).
        $prorrogas = TeamLicense::with('team')
            ->where('grant_type', 'prorroga')
            ->get();

        $prorrogasActivas  = $prorrogas->reject->is_expired->values();
        $prorrogasVencidas = $prorrogas->filter->is_expired->values();

        return view('admin.license-codes.index', [
            'codes'             => $codes,
            'presets'           => self::PRESETS,
            'prorrogasActivas'  => $prorrogasActivas,
            'prorrogasVencidas' => $prorrogasVencidas,
        ]);
    }

    /**
     * Habilita un periodo de prórroga directamente para una cuenta (equipo) por su ID.
     */
    public function grantProrroga(Request $request, TeamLicenseManager $svc)
    {
        $data = $request->validate([
            'team_id' => ['required', 'integer', 'exists:teams,id'],
            'days'    => ['required', 'integer', 'min:1', 'max:60'],
        ], [], [
            'team_id' => 'ID de cuenta',
            'days'    => 'días',
        ]);

        $team = Team::findOrFail($data['team_id']);
        $svc->grantProrroga($team, (int) $data['days']);

        return back()->with(
            'success',
            "Prórroga de {$data['days']} días habilitada para la cuenta #{$team->id} ({$team->name})."
        );
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

    /**
     * Reporte total de todas las cuentas (equipos) de la base.
     */
    public function accountsReport(Request $request)
    {
        // Incluye cuentas eliminadas (soft delete) para que sigan apareciendo
        // en el reporte con estado "Eliminada" y su fecha de eliminación.
        $teams = Team::withTrashed()
            ->with(['owner', 'license'])
            ->orderByDesc('id')
            ->paginate(40);

        // Cuentas eliminadas aún en periodo de retención (no purgadas) para la
        // notificación superior con días restantes.
        $deletedAccounts = Team::onlyTrashed()
            ->whereNull('purged_at')
            ->with('owner')
            ->orderBy('deleted_at')
            ->get();

        return view('admin.accounts-report', [
            'teams'           => $teams,
            'deletedAccounts' => $deletedAccounts,
        ]);
    }

    /**
     * Restaura una cuenta eliminada y le habilita un periodo de prórroga de 7 días.
     */
    public function restoreAccount($teamId, TeamLicenseManager $svc)
    {
        $team = Team::withTrashed()->findOrFail($teamId);

        if ($team->isPurged()) {
            return back()->with('error', "La cuenta #{$team->id} fue eliminada permanentemente y ya no se puede restaurar.");
        }

        if (! $team->trashed()) {
            return back()->with('error', "La cuenta #{$team->id} no está eliminada.");
        }

        $team->restore();

        // Re-vincula al dueño como miembro (Jetstream quita el pivote al eliminar).
        $owner = $team->owner;
        if ($owner && ! $team->hasUser($owner)) {
            $team->users()->attach($owner, ['role' => 'admin']);
        }

        // Si su licencia seguía VIGENTE, se conserva tal cual (misma fecha de
        // vencimiento). Si estaba vencida, bloqueada o sin licencia, se le
        // habilita un periodo de prórroga de 7 días.
        $lic = TeamLicense::where('team_id', $team->id)->first();
        $licenciaVigente = $lic && $lic->is_active && ! $lic->is_expired;

        $svc->forget($team);

        if ($licenciaVigente) {
            $vence = optional($lic->expires_at)->setTimezone($team->effectiveTimezone())->format('Y-m-d');
            return back()->with('success', "Cuenta #{$team->id} restaurada conservando su licencia vigente (vence {$vence}).");
        }

        $svc->grantProrroga($team, 7);

        return back()->with('success', "Cuenta #{$team->id} restaurada. Su licencia estaba vencida o bloqueada, se habilitó una prórroga de 7 días.");
    }

    /**
     * Elimina permanentemente los DATOS de la cuenta (libera recursos), pero
     * conserva el registro en el reporte con estado "Eliminada permanentemente".
     * No recuperable.
     */
    public function forceDeleteAccount($teamId)
    {
        $team = Team::withTrashed()->findOrFail($teamId);

        if (! $team->isPurged()) {
            $team->purgeData();
        }

        return back()->with('success', "Cuenta #{$team->id} eliminada permanentemente. Sus datos se borraron y el registro queda como referencia.");
    }

    /**
     * Bloquea una cuenta manualmente (conserva fechas para poder restaurarla).
     */
    public function blockAccount(Request $request, Team $team, TeamLicenseManager $svc)
    {
        $data = $request->validate([
            'note' => ['required', 'string', 'max:500'],
        ], [], ['note' => 'nota']);

        $lic = TeamLicense::firstOrCreate(['team_id' => $team->id], ['is_active' => true]);
        $lic->is_active = false;
        $lic->meta = $this->pushNote($lic->meta, 'bloqueo', $data['note']);
        $lic->save();

        $svc->forget($team);

        return back()->with('success', "Cuenta #{$team->id} bloqueada.");
    }

    /**
     * Habilita (desbloquea) una cuenta: restaura su licencia conservando las
     * fechas de vencimiento que tenía (licencia, prueba o prórroga vigente).
     */
    public function enableAccount(Request $request, Team $team, TeamLicenseManager $svc)
    {
        $data = $request->validate([
            'note' => ['required', 'string', 'max:500'],
        ], [], ['note' => 'nota']);

        $lic = TeamLicense::firstOrCreate(['team_id' => $team->id], ['is_active' => true]);
        $lic->is_active = true;
        $lic->meta = $this->pushNote($lic->meta, 'habilitacion', $data['note']);
        $lic->save();

        $svc->forget($team);

        return back()->with('success', "Cuenta #{$team->id} habilitada.");
    }

    /** Agrega una nota al historial guardado en meta. */
    private function pushNote(?array $meta, string $action, string $note): array
    {
        $meta ??= [];
        $meta['notes'][] = [
            'action' => $action,
            'note'   => $note,
            'at'     => now()->toDateTimeString(),
            'by'     => Auth::user()?->email,
        ];
        $meta['last_note'] = $note;

        return $meta;
    }
}
