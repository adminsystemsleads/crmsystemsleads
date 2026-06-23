<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\DealActivity;
use App\Models\Pipeline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DealActivityController extends Controller
{
    public function store(Request $request, Pipeline $pipeline, Deal $deal)
    {
        $data = $request->validate([
            'type'    => 'required|string|in:call,meeting,task',
            'subject' => 'required|string|max:255',
            'due_at'  => 'nullable|date',
            'notes'   => 'nullable|string|max:5000',
            'user_id' => 'nullable|exists:users,id',
            'notify_before' => 'nullable|integer|in:5,15,30,60,120,180',
        ]);

        DealActivity::create([
            'deal_id' => $deal->id,
            // Responsable elegido; si no se indica, el de la negociación o quien la crea.
            'user_id' => $data['user_id'] ?? $deal->responsible_id ?? Auth::id(),
            'type'    => $data['type'],
            'subject' => $data['subject'],
            'due_at'  => $data['due_at'] ?? null,
            'status'  => 'open',
            'notes'   => $data['notes'] ?? null,
            // Minutos antes del vencimiento para notificar (null = sin notificación).
            'notify_before' => ($data['due_at'] ?? null) ? ($data['notify_before'] ?? null) : null,
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
            'notify_before' => 'nullable|integer|in:5,15,30,60,120,180',
            'status'  => 'nullable|string|in:open,done',
        ]);

        $activity->fill([
            'type'    => $data['type'],
            'subject' => $data['subject'],
            'due_at'  => $data['due_at'] ?? null,
            'notes'   => $data['notes'] ?? null,
            'user_id' => $data['user_id'] ?? $activity->user_id,
            'notify_before' => ($data['due_at'] ?? null) ? ($data['notify_before'] ?? null) : null,
            'status'  => $data['status'] ?? $activity->status,
        ]);

        // Si cambió la fecha o el recordatorio, vuelve a habilitar el aviso.
        if ($activity->isDirty('due_at') || $activity->isDirty('notify_before')) {
            $activity->reminded_at = null;
        }

        $activity->save();

        return back()->with('status', 'Actividad actualizada.');
    }

    public function destroy(Pipeline $pipeline, Deal $deal, DealActivity $activity)
    {
        abort_unless((int) $activity->deal_id === (int) $deal->id, 404);

        $activity->delete();

        return back()->with('status', 'Actividad eliminada.');
    }
}
