<?php

namespace App\Http\Controllers;

use App\Models\QuickReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuickReplyController extends Controller
{
    private function teamId(): int
    {
        return Auth::user()->currentTeam->id;
    }

    /**
     * Lista todas las respuestas rápidas del usuario:
     *  - propias (personales)
     *  - del team (compartidas)
     */
    public function index(Request $request)
    {
        $teamId = $this->teamId();
        $userId = Auth::id();

        $q = QuickReply::where('team_id', $teamId)
            ->where(function ($w) use ($userId) {
                $w->where('is_team_wide', true)
                  ->orWhere('user_id', $userId);
            })
            ->orderByDesc('times_used')
            ->orderBy('title');

        if ($search = trim((string) $request->query('q'))) {
            $q->where(function ($w) use ($search) {
                $w->where('title',    'like', "%{$search}%")
                  ->orWhere('shortcut','like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $items = $q->limit(100)->get(['id', 'shortcut', 'title', 'content', 'is_team_wide', 'user_id', 'times_used']);

        return response()->json([
            'ok'      => true,
            'replies' => $items->map(fn($r) => [
                'id'           => $r->id,
                'shortcut'     => $r->shortcut,
                'title'        => $r->title,
                'content'      => $r->content,
                'is_team_wide' => $r->is_team_wide,
                'is_mine'      => $r->user_id === $userId,
                'times_used'   => $r->times_used,
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'shortcut'     => 'nullable|string|max:50|alpha_dash',
            'title'        => 'required|string|max:150',
            'content'      => 'required|string|max:4000',
            'is_team_wide' => 'nullable|boolean',
        ]);

        $reply = QuickReply::create([
            'team_id'      => $this->teamId(),
            'user_id'      => Auth::id(),
            'shortcut'     => $data['shortcut'] ?? null,
            'title'        => $data['title'],
            'content'      => $data['content'],
            'is_team_wide' => $request->boolean('is_team_wide', true),
        ]);

        return response()->json(['ok' => true, 'id' => $reply->id]);
    }

    public function update(Request $request, QuickReply $quickReply)
    {
        abort_unless($quickReply->team_id === $this->teamId(), 404);
        // Solo el creador puede editar (o si no tiene creador, cualquiera del team)
        if ($quickReply->user_id && $quickReply->user_id !== Auth::id()) {
            abort(403, 'Solo el creador puede editar esta respuesta.');
        }

        $data = $request->validate([
            'shortcut'     => 'nullable|string|max:50|alpha_dash',
            'title'        => 'required|string|max:150',
            'content'      => 'required|string|max:4000',
            'is_team_wide' => 'nullable|boolean',
        ]);

        $quickReply->update([
            'shortcut'     => $data['shortcut'] ?? null,
            'title'        => $data['title'],
            'content'      => $data['content'],
            'is_team_wide' => $request->boolean('is_team_wide', $quickReply->is_team_wide),
        ]);

        return response()->json(['ok' => true]);
    }

    public function destroy(QuickReply $quickReply)
    {
        abort_unless($quickReply->team_id === $this->teamId(), 404);
        if ($quickReply->user_id && $quickReply->user_id !== Auth::id()) {
            abort(403);
        }

        $quickReply->delete();
        return response()->json(['ok' => true]);
    }

    /**
     * Marca como usada (incrementa contador).
     */
    public function used(QuickReply $quickReply)
    {
        abort_unless($quickReply->team_id === $this->teamId(), 404);
        $quickReply->increment('times_used');
        return response()->json(['ok' => true]);
    }
}
