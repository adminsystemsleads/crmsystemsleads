<?php

namespace App\Http\Controllers;

use App\Models\ChatTag;
use App\Models\WhatsappConversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatTagController extends Controller
{
    private function teamId(): int
    {
        return Auth::user()->currentTeam->id;
    }

    /** Lista todas las etiquetas del team */
    public function index()
    {
        $tags = ChatTag::where('team_id', $this->teamId())
            ->orderBy('name')
            ->get(['id', 'name', 'color']);

        return response()->json(['ok' => true, 'tags' => $tags]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:80',
            'color' => 'nullable|string|max:20',
        ]);

        $tag = ChatTag::firstOrCreate(
            ['team_id' => $this->teamId(), 'name' => $data['name']],
            ['color'   => $data['color'] ?? 'indigo']
        );

        // Si se quería actualizar el color
        if (!empty($data['color']) && $tag->color !== $data['color']) {
            $tag->update(['color' => $data['color']]);
        }

        return response()->json(['ok' => true, 'tag' => $tag]);
    }

    public function update(Request $request, ChatTag $chatTag)
    {
        abort_unless($chatTag->team_id === $this->teamId(), 404);

        $data = $request->validate([
            'name'  => 'required|string|max:80',
            'color' => 'nullable|string|max:20',
        ]);

        $chatTag->update($data);
        return response()->json(['ok' => true, 'tag' => $chatTag]);
    }

    public function destroy(ChatTag $chatTag)
    {
        abort_unless($chatTag->team_id === $this->teamId(), 404);
        $chatTag->delete();
        return response()->json(['ok' => true]);
    }

    /** Etiquetas asignadas a una conversación */
    public function conversationTags(WhatsappConversation $conversation)
    {
        abort_unless($conversation->team_id === $this->teamId(), 404);

        $tags = $conversation->tags()->get(['chat_tags.id', 'name', 'color']);
        return response()->json(['ok' => true, 'tags' => $tags]);
    }

    /** Sincroniza las etiquetas de una conversación */
    public function syncConversationTags(Request $request, WhatsappConversation $conversation)
    {
        abort_unless($conversation->team_id === $this->teamId(), 404);

        $data = $request->validate([
            'tag_ids'   => 'nullable|array',
            'tag_ids.*' => 'integer|exists:chat_tags,id',
        ]);

        // Filtrar solo IDs del team
        $ids = ChatTag::where('team_id', $this->teamId())
            ->whereIn('id', $data['tag_ids'] ?? [])
            ->pluck('id')->all();

        $conversation->tags()->sync($ids);

        return response()->json(['ok' => true, 'tags' => $conversation->tags()->get(['chat_tags.id','name','color'])]);
    }
}
