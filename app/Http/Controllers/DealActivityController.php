<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\DealActivity;
use App\Models\Pipeline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DealActivityController extends Controller
{
    /** Minutos válidos para los recordatorios. */
    private const VALID_MINUTES = [5, 15, 30, 60, 120, 180];

    public function store(Request $request, Pipeline $pipeline, Deal $deal)
    {
        $data = $request->validate([
            'type'    => 'required|string|in:call,meeting,task',
            'subject' => 'required|string|max:255',
            'due_at'  => 'nullable|date',
            'notes'   => 'nullable|string|max:5000',
            'user_id' => 'nullable|exists:users,id',
            'notify_minutes'   => 'nullable|array',
            'notify_minutes.*' => 'integer|in:'.implode(',', self::VALID_MINUTES),
        ]);

        $minutes = $this->cleanMinutes($data['notify_minutes'] ?? [], $data['due_at'] ?? null);
        $dueAt   = $this->dueAtForStorage($data['due_at'] ?? null, $deal);

        DealActivity::create([
            'deal_id' => $deal->id,
            'user_id' => $data['user_id'] ?? $deal->responsible_id ?? Auth::id(),
            'type'    => $data['type'],
            'subject' => $data['subject'],
            'due_at'  => $dueAt,
            'status'  => 'open',
            'notes'   => $data['notes'] ?? null,
            'notify_minutes'   => $minutes ?: null,
            'reminded_minutes' => [],
        ]);

        return back()->with('status', 'Actividad creada.');
    }

    public function update(Request $request, Pipeline $pipeline, Deal $deal, DealActivity $activity)
    {
        abort_unless((int) $activity->deal_id === (int) $deal->id, 404);

        $data = $request->validate([
            'type'    => 'required|string|in:call,meeting,task',
            'subject' => 'required|string|max:255',
            'due_at'  => 'nullable|date',
            'notes'   => 'nullable|string|max:5000',
            'user_id' => 'nullable|exists:users,id',
            'notify_minutes'   => 'nullable|array',
            'notify_minutes.*' => 'integer|in:'.implode(',', self::VALID_MINUTES),
            'status'  => 'nullable|string|in:open,done,lost',
        ]);

        $minutes = $this->cleanMinutes($data['notify_minutes'] ?? [], $data['due_at'] ?? null);
        $dueAt   = $this->dueAtForStorage($data['due_at'] ?? null, $deal);

        $activity->fill([
            'type'    => $data['type'],
            'subject' => $data['subject'],
            'due_at'  => $dueAt,
            'notes'   => $data['notes'] ?? null,
            'user_id' => $data['user_id'] ?? $activity->user_id,
            'notify_minutes' => $minutes ?: null,
            'status'  => $data['status'] ?? $activity->status,
        ]);

        // Si cambió la fecha o los recordatorios, reinicia los avisos enviados.
        if ($activity->isDirty('due_at') || $activity->isDirty('notify_minutes')) {
            $activity->reminded_minutes = [];
        }

        $activity->save();

        return back()->with('status', 'Actividad actualizada.');
    }

    /** Marca como completada (respetando la ventana de 3h salvo admin). */
    public function complete(Pipeline $pipeline, Deal $deal, DealActivity $activity)
    {
        abort_unless((int) $activity->deal_id === (int) $deal->id, 404);

        if (! $activity->completableBy(Auth::user(), $deal->team)) {
            return back()
                ->with('flash.banner', 'Ya no se puede marcar como completada: pasaron más de '
                    .DealActivity::GRACE_HOURS.' horas del vencimiento. Solo un administrador puede hacerlo.')
                ->with('flash.bannerStyle', 'danger');
        }

        $activity->update(['status' => 'done']);

        return back()->with('status', 'Actividad marcada como completada.');
    }

    public function destroy(Pipeline $pipeline, Deal $deal, DealActivity $activity)
    {
        abort_unless((int) $activity->deal_id === (int) $deal->id, 404);

        $activity->delete();

        return back()->with('status', 'Actividad eliminada.');
    }

    /**
     * Convierte la fecha/hora del formulario (en la zona horaria del equipo)
     * a la zona de almacenamiento (config('app.timezone'), normalmente UTC).
     */
    private function dueAtForStorage(?string $input, Deal $deal): ?\Carbon\Carbon
    {
        if (empty($input)) {
            return null;
        }
        $tz = $deal->team?->effectiveTimezone() ?? config('app.timezone');

        return \Carbon\Carbon::parse($input, $tz)->setTimezone(config('app.timezone'));
    }

    /** Normaliza la lista de minutos: únicos, válidos y solo si hay fecha límite. */
    private function cleanMinutes(array $minutes, ?string $dueAt): array
    {
        if (!$dueAt) {
            return [];
        }
        $minutes = array_values(array_unique(array_map('intval', $minutes)));
        $minutes = array_values(array_filter($minutes, fn ($m) => in_array($m, self::VALID_MINUTES, true)));
        sort($minutes);
        return $minutes;
    }
}
