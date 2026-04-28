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



    public function index(Request $request)
    {
        $team = $this->currentTeam();

        $accountId = $request->query('account_id');

        $accounts = WhatsappAccount::where('team_id', $team->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $conversationsQuery = WhatsappConversation::where('team_id', $team->id)
            ->with('account')
            ->orderByDesc('last_message_at');

        if ($accountId) {
            $conversationsQuery->where('whatsapp_account_id', $accountId);
        }

        $conversations = $conversationsQuery->paginate(20);

        return view('whatsapp.inbox.index', compact('accounts', 'conversations', 'accountId'));
    }

    public function show(WhatsappConversation $conversation)
    {
        $team = $this->currentTeam();
        abort_unless($conversation->team_id === $team->id, 404);

        $conversation->load(['account', 'messages.sentBy', 'deals.contact']);

        // Deal actual (último enlazado)
        $currentDeal = $conversation->deals()->orderByDesc('whatsapp_conversation_deals.created_at')->first();

        return view('whatsapp.inbox.show', compact('conversation', 'currentDeal'));
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
