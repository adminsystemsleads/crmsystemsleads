<?php

namespace App\Http\Controllers;

use App\Models\Pipeline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /** Lista las últimas notificaciones + contador de no leídas (JSON para la campana). */
    public function index()
    {
        $user = Auth::user();

        $items = $user->notifications()
            ->latest()
            ->take(20)
            ->get()
            ->map(fn ($n) => [
                'id'   => $n->id,
                'data' => $n->data,
                'read' => $n->read_at !== null,
                'time' => optional($n->created_at)->diffForHumans(),
            ]);

        return response()->json([
            'unread' => $user->unreadNotifications()->count(),
            'items'  => $items,
        ]);
    }

    /** Marca una notificación como leída. */
    public function read(string $id)
    {
        Auth::user()->notifications()
            ->where('id', $id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['ok' => true]);
    }

    /** Marca todas como leídas. */
    public function readAll()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return response()->json(['ok' => true]);
    }

    /** Devuelve las preferencias del usuario + embudos disponibles (para la tuerca de config). */
    public function prefs()
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        $pipelines = $team
            ? Pipeline::where('team_id', $team->id)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->filter(fn ($p) => $user->canViewPipeline($p))
                ->map(fn ($p) => ['id' => $p->id, 'name' => $p->name])
                ->values()
            : collect();

        return response()->json([
            'prefs'     => $user->notifPrefs(),
            'pipelines' => $pipelines,
        ]);
    }

    /** Guarda las preferencias de notificación. */
    public function savePrefs(Request $request)
    {
        $data = $request->validate([
            'enabled'       => 'boolean',
            'sound'         => 'boolean',
            'deal_assigned' => 'boolean',
            'activity_due'  => 'boolean',
            'pipelines'     => 'nullable|array',
            'pipelines.*'   => 'integer',
        ]);

        $user = Auth::user();

        $prefs = array_merge($user->notifPrefs(), [
            'enabled'       => (bool) ($data['enabled'] ?? true),
            'sound'         => (bool) ($data['sound'] ?? true),
            'deal_assigned' => (bool) ($data['deal_assigned'] ?? true),
            'activity_due'  => (bool) ($data['activity_due'] ?? true),
            // null = todos los embudos (incluye los futuros); array = solo esos.
            'pipelines'     => array_key_exists('pipelines', $data) ? $data['pipelines'] : null,
        ]);

        $user->notification_prefs = $prefs;
        $user->save();

        return response()->json(['ok' => true, 'prefs' => $prefs]);
    }
}
