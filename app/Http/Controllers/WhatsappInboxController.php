<?php


namespace App\Http\Controllers;

use App\Models\WhatsappAccount;
use App\Models\WhatsappConversation;
use App\Models\WhatsappMessage;
use App\Services\WhatsappCloudService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WhatsappInboxController extends Controller
{
    protected function currentTeam()
    {
        return Auth::user()->currentTeam;
    }


    public function messageJson(\App\Models\WhatsappMessage $message)
{
    // Opcional: validar que el usuario tenga acceso al team/conversation
    // abort_unless($message->team_id === auth()->user()->current_team_id, 403);

    return response()->json([
        'id' => $message->id,
        'message_id' => $message->message_id,
        'direction' => $message->direction,
        'type' => $message->type,
        'body' => $message->body,
        'caption' => $message->caption ?? null,
        'public_url' => $message->public_url ?? null,
        'mime_type' => $message->mime_type ?? null,
        'filename' => $message->filename ?? null,
        'created_at' => optional($message->created_at)->toIso8601String(),
        'sent_by' => $message->sentBy ? ['name' => $message->sentBy->name] : null,
    ]);
}



    protected function sidebarConversations(int $teamId, ?string $accountId, string $status = 'all'): \Illuminate\Database\Eloquent\Collection
    {
        $q = WhatsappConversation::where('team_id', $teamId)
            ->with('account')
            ->orderByDesc('last_message_at')
            ->limit(60);

        if ($accountId) {
            $q->where('whatsapp_account_id', $accountId);
        }
        if ($status === 'open') {
            $q->where('status', 'open');
        } elseif ($status === 'closed') {
            $q->where('status', 'closed');
        }

        return $q->get();
    }

    public function index(Request $request)
    {
        $team      = $this->currentTeam();
        $accountId = $request->query('account_id');
        $status    = $request->query('status', 'all');

        $accounts = WhatsappAccount::where('team_id', $team->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $conversations = $this->sidebarConversations($team->id, $accountId, $status);

        // Redirect to first conversation so the user lands in the 3-panel view
        if ($conversations->isNotEmpty() && !$request->expectsJson()) {
            return redirect()->route('whatsapp.inbox.show', $conversations->first())
                ->withInput($request->only('account_id', 'status'));
        }

        return view('whatsapp.inbox.index', compact('accounts', 'conversations', 'accountId', 'status'));
    }

    public function show(WhatsappConversation $conversation, Request $request)
    {
        $team = $this->currentTeam();
        abort_unless($conversation->team_id === $team->id, 404);

        $conversation->load(['account', 'messages.sentBy', 'deals']);

        $currentDeal = $conversation->deals()
            ->orderByDesc('whatsapp_conversation_deals.created_at')
            ->first();

        $accountId = $request->query('account_id');
        $status    = $request->query('status', 'all');

        $accounts = WhatsappAccount::where('team_id', $team->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $conversations = $this->sidebarConversations($team->id, $accountId, $status);

        return view('whatsapp.inbox.show', compact(
            'conversation', 'currentDeal', 'conversations', 'accounts', 'accountId', 'status'
        ));
    }

    public function newMessages(WhatsappConversation $conversation, Request $request)
    {
        $team = $this->currentTeam();
        abort_unless($conversation->team_id === $team->id, 404);

        $afterId = (int) $request->query('after', 0);

        $messages = $conversation->messages()
            ->with('sentBy')
            ->when($afterId, fn($q) => $q->where('id', '>', $afterId))
            ->orderBy('id')
            ->limit(30)
            ->get()
            ->map(fn($m) => [
                'id'              => $m->id,
                'message_id'      => $m->message_id,
                'direction'       => $m->direction,
                'type'            => $m->type,
                'body'            => $m->body,
                'caption'         => $m->caption,
                'public_url'      => $m->public_url,
                'mime_type'       => $m->mime_type,
                'filename'        => $m->filename,
                'created_at'      => $m->created_at?->toIso8601String(),
                'sent_by'         => $m->sentBy ? ['name' => $m->sentBy->name] : null,
            ]);

        return response()->json($messages);
    }

    public function send(Request $request, WhatsappConversation $conversation, WhatsappCloudService $wa)
    {
        $team = $this->currentTeam();
        abort_unless($conversation->team_id === $team->id, 404);

        $data = $request->validate([
            'message' => 'required|string|max:4000',
        ]);

        $account = $conversation->account;

        $res = $wa->sendText($account, $conversation->contact_phone, $data['message']);

        $metaMessageId = $res['messages'][0]['id'] ?? null;

        WhatsappMessage::create([
            'team_id' => $team->id,
            'whatsapp_conversation_id' => $conversation->id,
            'direction' => 'outbound',
            'message_id' => $metaMessageId,
            'type' => 'text',
            'body' => $data['message'],
            'raw_payload' => $res,
            'sent_by_user_id' => Auth::id(),
        ]);

        $conversation->update([
            'last_message_at' => now(),
            'last_message_preview' => mb_substr($data['message'], 0, 180),
        ]);

        return back()->with('status', 'Mensaje enviado.');
    }
}
