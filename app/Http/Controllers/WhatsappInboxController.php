<?php


namespace App\Http\Controllers;

use App\Models\WhatsappAccount;
use App\Models\WhatsappConversation;
use App\Models\WhatsappMessage;
use App\Services\WhatsappCloudService;
use App\Services\WhatsappTemplateService;
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



    protected function sidebarConversations(
        int $teamId,
        ?string $accountId,
        string $status = 'all',
        ?string $search = null,
        ?int $tagId = null
    ): \Illuminate\Database\Eloquent\Collection
    {
        $q = WhatsappConversation::where('team_id', $teamId)
            ->with(['account', 'tags:chat_tags.id,name,color'])
            ->orderByDesc('last_message_at')
            ->limit(120);

        if ($accountId) {
            $q->where('whatsapp_account_id', $accountId);
        }
        if ($status === 'open') {
            $q->where('status', 'open');
        } elseif ($status === 'closed') {
            $q->where('status', 'closed');
        }

        if ($search) {
            $term = '%' . str_replace(['%', '_'], ['\%', '\_'], $search) . '%';
            $q->where(function ($w) use ($term) {
                $w->where('contact_name',  'like', $term)
                  ->orWhere('contact_phone', 'like', $term)
                  ->orWhere('wa_id',         'like', $term);
            });
        }

        if ($tagId) {
            $q->whereHas('tags', fn($w) => $w->where('chat_tags.id', $tagId));
        }

        return $q->get();
    }

    public function index(Request $request)
    {
        $team      = $this->currentTeam();
        $accountId = $request->query('account_id');
        $status    = $request->query('status', 'all');
        $search    = $request->query('q');
        $tagId     = $request->query('tag_id') ? (int) $request->query('tag_id') : null;

        $accounts = WhatsappAccount::where('team_id', $team->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $conversations = $this->sidebarConversations($team->id, $accountId, $status, $search, $tagId);

        // Redirect to first conversation so the user lands in the 3-panel view
        if ($conversations->isNotEmpty() && !$request->expectsJson() && !$search && !$tagId) {
            return redirect()->route('whatsapp.inbox.show', $conversations->first())
                ->withInput($request->only('account_id', 'status'));
        }

        $allTags = \App\Models\ChatTag::where('team_id', $team->id)->orderBy('name')->get(['id', 'name', 'color']);

        return view('whatsapp.inbox.index', compact('accounts', 'conversations', 'accountId', 'status', 'search', 'tagId', 'allTags'));
    }

    public function show(WhatsappConversation $conversation, Request $request)
    {
        $team = $this->currentTeam();
        abort_unless($conversation->team_id === $team->id, 404);

        $conversation->load(['account.aiAssistant', 'messages.sentBy', 'deals']);

        $currentDeal = $conversation->deals()
            ->with(['pipeline', 'stage'])
            ->orderByDesc('whatsapp_conversation_deals.created_at')
            ->first();

        $accountId = $request->query('account_id');
        $status    = $request->query('status', 'all');
        $search    = $request->query('q');
        $tagId     = $request->query('tag_id') ? (int) $request->query('tag_id') : null;

        $accounts = WhatsappAccount::where('team_id', $team->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $pipelines = \App\Models\Pipeline::where('team_id', $team->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $conversations = $this->sidebarConversations($team->id, $accountId, $status, $search, $tagId);

        $aiAssistant = $conversation->account?->aiAssistant;
        $hasAi       = $aiAssistant?->is_active ?? false;

        $allTags = \App\Models\ChatTag::where('team_id', $team->id)->orderBy('name')->get(['id', 'name', 'color']);
        $conversation->load('tags:chat_tags.id,name,color');

        return view('whatsapp.inbox.show', compact(
            'conversation', 'currentDeal', 'conversations', 'accounts', 'accountId', 'status',
            'pipelines', 'aiAssistant', 'hasAi', 'search', 'tagId', 'allTags'
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

        $conversation->load(['account', 'messages.sentBy', 'deals', 'tags:chat_tags.id,name,color']);

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

        // Calcular ventana de 24h: el último mensaje INBOUND del cliente
        // Usamos reorder() porque la relación messages() ya define orderBy ASC por defecto.
        $lastInbound = $conversation->messages()
            ->where('direction', 'inbound')
            ->reorder('created_at', 'desc')
            ->first();

        $windowExpired = true;
        $windowExpiresAt = null;
        if ($lastInbound && $lastInbound->created_at) {
            $expires = $lastInbound->created_at->copy()->addHours(24);
            $windowExpiresAt = $expires->toIso8601String();
            $windowExpired   = $expires->isPast();
        }

        return response()->json([
            'id'              => $conversation->id,
            'contact_name'    => $conversation->contact_name,
            'contact_phone'   => $conversation->contact_phone,
            'status'          => $conversation->status,
            'ai_active'       => (bool) $conversation->ai_active,
            'account_name'    => $conversation->account?->name,
            'last_message_at' => $conversation->last_message_at?->diffForHumans(),
            'window_expired'  => $windowExpired,
            'window_expires_at' => $windowExpiresAt,
            'tags'            => $conversation->tags->map(fn($t) => [
                'id' => $t->id, 'name' => $t->name, 'color' => $t->color,
            ])->all(),
            'messages'        => $messages,
            'current_deal'    => $dealData,
            'pipelines'       => $pipelines,
            'has_ai'          => $conversation->account?->aiAssistant?->is_active ?? false,
            'urls'            => [
                'send'             => route('whatsapp.inbox.send', $conversation),
                'messages'         => route('whatsapp.inbox.messages', $conversation),
                'create_deal'      => route('whatsapp.inbox.deal.create', $conversation),
                'ai_toggle'        => route('whatsapp.inbox.ai.toggle', $conversation),
                'page'             => route('whatsapp.inbox.show', $conversation),
                'templates'        => route('whatsapp.inbox.templates', $conversation),
                'send_template'    => route('whatsapp.inbox.send-template', $conversation),
            ],
        ]);
    }

    public function toggleAi(WhatsappConversation $conversation)
    {
        $team = $this->currentTeam();
        abort_unless($conversation->team_id === $team->id, 404);

        $conversation->update(['ai_active' => !$conversation->ai_active]);

        return response()->json(['ai_active' => (bool) $conversation->ai_active]);
    }

    public function sidebarPoll(Request $request)
    {
        $team      = $this->currentTeam();
        $accountId = $request->query('account_id');
        $status    = $request->query('status', 'all');
        $search    = $request->query('q');
        $tagId     = $request->query('tag_id') ? (int) $request->query('tag_id') : null;

        $conversations = $this->sidebarConversations($team->id, $accountId, $status, $search, $tagId)
            ->map(fn($c) => [
                'id'                   => $c->id,
                'contact_name'         => $c->contact_name,
                'contact_phone'        => $c->contact_phone,
                'last_message_preview' => $c->last_message_preview,
                'last_message_at'      => $c->last_message_at?->timestamp ?? 0,
                'status'               => $c->status,
                'tags'                 => $c->tags->map(fn($t) => ['id' => $t->id, 'name' => $t->name, 'color' => $t->color])->all(),
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
            'ai_active'            => false, // agente tomó control → pausa el bot
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

    /* ============ PLANTILLAS DE META ============ */

    public function templates(WhatsappConversation $conversation, Request $request, WhatsappTemplateService $tpl)
    {
        $team = $this->currentTeam();
        abort_unless($conversation->team_id === $team->id, 404);

        $force = $request->boolean('refresh');
        $result = $tpl->listTemplates($conversation->account, $force);

        return response()->json($result);
    }

    public function sendTemplate(Request $request, WhatsappConversation $conversation, WhatsappTemplateService $tpl)
    {
        $team = $this->currentTeam();
        abort_unless($conversation->team_id === $team->id, 404);

        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'language'       => 'required|string|max:20',
            'body_params'    => 'nullable|array',
            'body_params.*'  => 'string|max:500',
            'header_params'  => 'nullable|array',
            'header_params.*'=> 'string|max:500',
            'preview'        => 'nullable|string|max:1000',
        ]);

        $account = $conversation->account;

        $result = $tpl->sendTemplate(
            account:      $account,
            toPhone:      $conversation->contact_phone,
            name:         $data['name'],
            language:     $data['language'],
            bodyParams:   $data['body_params'] ?? [],
            headerParams: $data['header_params'] ?? []
        );

        if (!$result['ok']) {
            return response()->json([
                'ok'      => false,
                'message' => $result['message'] ?? 'Error al enviar plantilla',
            ], 422);
        }

        $metaMessageId = $result['response']['messages'][0]['id'] ?? null;
        $bodyPreview   = $data['preview'] ?? "[plantilla: {$data['name']}]";

        $message = WhatsappMessage::create([
            'team_id'                  => $team->id,
            'whatsapp_conversation_id' => $conversation->id,
            'direction'                => 'outbound',
            'message_id'               => $metaMessageId,
            'type'                     => 'template',
            'body'                     => $bodyPreview,
            'raw_payload'              => json_encode($result['response']),
            'sent_by_user_id'          => Auth::id(),
        ]);

        $conversation->update([
            'last_message_at'      => now(),
            'last_message_preview' => mb_substr($bodyPreview, 0, 180),
            'ai_active'            => false,
        ]);

        return response()->json([
            'ok'         => true,
            'id'         => $message->id,
            'message_id' => $message->message_id,
            'direction'  => 'outbound',
            'type'       => 'template',
            'body'       => $message->body,
            'created_at' => $message->created_at?->toIso8601String(),
            'sent_by'    => ['name' => Auth::user()->name],
        ]);
    }
}
