<?php

namespace App\Http\Controllers;

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
}
