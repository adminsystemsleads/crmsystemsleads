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
            ->with(['pipeline', 'stage'])
            ->orderByDesc('whatsapp_conversation_deals.created_at')
            ->first();

        $accountId = $request->query('account_id');
        $status    = $request->query('status', 'all');

        $accounts = WhatsappAccount::where('team_id', $team->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $pipelines = \App\Models\Pipeline::where('team_id', $team->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $conversations = $this->sidebarConversations($team->id, $accountId, $status);

        return view('whatsapp.inbox.show', compact(
            'conversation', 'currentDeal', 'conversations', 'accounts', 'accountId', 'status', 'pipelines'
        ));
    }

    public function createDeal(Request $request, WhatsappConversation $conversation)
    {
        $team = $this->currentTeam();
        abort_unless($conversation->team_id === $team->id, 404);

        $data = $request->validate([
            'pipeline_id' => 'required|integer|exists:pipelines,id',
            'title'       => 'nullable|string|max:255',
        ]);

        // Verificar que el pipeline pertenece al team
        $pipeline = \App\Models\Pipeline::where('id', $data['pipeline_id'])
            ->where('team_id', $team->id)
            ->firstOrFail();

        $firstStage = \App\Models\PipelineStage::where('pipeline_id', $pipeline->id)
            ->orderBy('sort_order')
            ->first();

        abort_unless($firstStage, 422, 'El pipeline no tiene fases.');

        $contactName = $conversation->contact_name ?? $conversation->contact_phone ?? 'WhatsApp';
        $title = $data['title'] ?: $contactName . ' - WhatsApp';
        $phone = $conversation->contact_phone ?? $conversation->wa_id;

        // Buscar contacto existente por teléfono en el team
        $contact = $phone
            ? \App\Models\Contact::where('team_id', $team->id)
                ->where('phone', 'like', '%' . ltrim($phone, '+'))
                ->first()
            : null;

        // Si no existe, crear uno nuevo
        if (!$contact && $phone) {
            $nameParts = explode(' ', trim($conversation->contact_name ?? ''), 2);
            $contact = \App\Models\Contact::create([
                'team_id'    => $team->id,
                'owner_id'   => Auth::id(),
                'first_name' => $nameParts[0] ?? $phone,
                'last_name'  => $nameParts[1] ?? '',
                'name'       => $conversation->contact_name ?? $phone,
                'phone'      => $phone,
                'status'     => 'new',
                'source'     => 'whatsapp',
            ]);
        }

        $deal = \App\Models\Deal::create([
            'team_id'        => $team->id,
            'owner_id'       => Auth::id(),
            'pipeline_id'    => $pipeline->id,
            'stage_id'       => $firstStage->id,
            'contact_id'     => $contact?->id,
            'wa_id'          => $conversation->wa_id ?? $phone,
            'responsible_id' => Auth::id(),
            'title'          => $title,
            'amount'         => null,
            'currency'       => 'PEN',
            'status'         => 'open',
            'description'    => 'Negociación creada desde WhatsApp inbox.',
        ]);

        $conversation->deals()->attach($deal->id, [
            'started_at' => now(),
            'ended_at'   => null,
        ]);

        return redirect()->route('whatsapp.inbox.show', $conversation)
            ->with('deal_created', $deal->id);
    }

    public function panel(WhatsappConversation $conversation, Request $request)
    {
        $team = $this->currentTeam();
        abort_unless($conversation->team_id === $team->id, 404);

        $conversation->load(['account', 'messages.sentBy', 'deals']);

        $currentDeal = $conversation->deals()
            ->with(['pipeline', 'stage'])
            ->orderByDesc('whatsapp_conversation_deals.created_at')
            ->first();

        $messages = $conversation->messages->map(fn($m) => [
            'id'         => $m->id,
            'message_id' => $m->message_id,
            'direction'  => $m->direction,
            'type'       => $m->type,
            'body'       => $m->body,
            'caption'    => $m->caption,
            'public_url' => $m->public_url,
            'mime_type'  => $m->mime_type,
            'filename'   => $m->filename,
            'created_at' => $m->created_at?->toIso8601String(),
            'sent_by'    => $m->sentBy ? ['name' => $m->sentBy->name] : null,
        ]);

        $pipelines = \App\Models\Pipeline::where('team_id', $team->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name']);

        $dealData = null;
        if ($currentDeal) {
            $dealPipeline = optional($currentDeal->pipeline);
            $dealStage    = is_object($currentDeal->stage) ? $currentDeal->stage : null;
            $dealData = [
                'id'            => $currentDeal->id,
                'title'         => $currentDeal->title,
                'status'        => $currentDeal->status,
                'pipeline_name' => $dealPipeline->name ?? '',
                'stage_name'    => $dealStage?->name ?? '',
                'edit_url'      => route('deals.edit', [$currentDeal->pipeline_id, $currentDeal->id]),
            ];
        }

        return response()->json([
            'id'              => $conversation->id,
            'contact_name'    => $conversation->contact_name,
            'contact_phone'   => $conversation->contact_phone,
            'status'          => $conversation->status,
            'account_name'    => $conversation->account?->name,
            'last_message_at' => $conversation->last_message_at?->diffForHumans(),
            'messages'        => $messages,
            'current_deal'    => $dealData,
            'pipelines'       => $pipelines,
            'urls'            => [
                'send'        => route('whatsapp.inbox.send', $conversation),
                'messages'    => route('whatsapp.inbox.messages', $conversation),
                'create_deal' => route('whatsapp.inbox.deal.create', $conversation),
                'page'        => route('whatsapp.inbox.show', $conversation),
            ],
        ]);
    }

    public function sidebarPoll(Request $request)
    {
        $team      = $this->currentTeam();
        $accountId = $request->query('account_id');
        $status    = $request->query('status', 'all');

        $conversations = $this->sidebarConversations($team->id, $accountId, $status)
            ->map(fn($c) => [
                'id'                   => $c->id,
                'contact_name'         => $c->contact_name,
                'contact_phone'        => $c->contact_phone,
                'last_message_preview' => $c->last_message_preview,
                'last_message_at'      => $c->last_message_at?->timestamp ?? 0,
                'status'               => $c->status,
            ]);

        return response()->json($conversations);
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

        $hasFile = $request->hasFile('media');

        $request->validate(array_filter([
            'message' => $hasFile ? 'nullable|string|max:4000' : 'required|string|max:4000',
            'media'   => $hasFile ? 'required|file|mimes:jpeg,jpg,png,gif,webp,mp4,3gp|max:16384' : null,
        ]));

        $account = $conversation->account;
        $msgType = 'text';
        $msgBody = $request->input('message', '');
        $publicUrl   = null;
        $storagePath = null;
        $mimeType    = null;
        $filename    = null;

        if ($hasFile) {
            $file     = $request->file('media');
            $mimeType = $file->getMimeType();
            $filename = $file->getClientOriginalName();
            $msgType  = str_starts_with($mimeType, 'video/') ? 'video' : 'image';
            $caption  = $msgBody ?: null;

            // 1) Subir a Meta
            $mediaId = $wa->uploadMedia($account, $file->getRealPath(), $mimeType, $filename);

            // 2) Enviar por WhatsApp
            $res = $wa->sendMedia($account, $conversation->contact_phone, $msgType, $mediaId, $caption);

            // 3) Guardar copia local en storage/public
            $path = "whatsapp/{$team->id}/conversations/{$conversation->id}/" . uniqid() . '_' . $filename;
            \Illuminate\Support\Facades\Storage::disk('public')->put($path, file_get_contents($file->getRealPath()));
            $storagePath = $path;
            $publicUrl   = \Illuminate\Support\Facades\Storage::disk('public')->url($path);
            $msgBody     = '[' . ($msgType === 'video' ? 'video' : 'imagen') . ']';
        } else {
            $res = $wa->sendText($account, $conversation->contact_phone, $msgBody);
        }

        $metaMessageId = $res['messages'][0]['id'] ?? null;

        $message = WhatsappMessage::create([
            'team_id'                 => $team->id,
            'whatsapp_conversation_id'=> $conversation->id,
            'direction'               => 'outbound',
            'message_id'              => $metaMessageId,
            'type'                    => $msgType,
            'body'                    => $msgBody,
            'mime_type'               => $mimeType,
            'filename'                => $filename,
            'storage_path'            => $storagePath,
            'public_url'              => $publicUrl,
            'raw_payload'             => json_encode($res),
            'sent_by_user_id'         => Auth::id(),
        ]);

        $conversation->update([
            'last_message_at'      => now(),
            'last_message_preview' => mb_substr($msgBody, 0, 180),
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok'         => true,
                'id'         => $message->id,
                'message_id' => $message->message_id,
                'direction'  => 'outbound',
                'type'       => $message->type,
                'body'       => $message->body,
                'public_url' => $message->public_url,
                'mime_type'  => $message->mime_type,
                'filename'   => $message->filename,
                'created_at' => $message->created_at?->toIso8601String(),
                'sent_by'    => ['name' => Auth::user()->name],
            ]);
        }

        return back()->with('status', 'Mensaje enviado.');
    }
}
