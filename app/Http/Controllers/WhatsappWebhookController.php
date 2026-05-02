<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\PipelineStage;
use App\Models\WhatsappAccount;
use App\Models\WhatsappConversation;
use App\Models\WhatsappMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Events\WhatsappMessageReceived;
use App\Services\WhatsappCloudMediaService;

class WhatsappWebhookController extends Controller
{
    // GET webhook (verificación)
    public function verify(Request $request)
{
    // Meta manda con puntos: hub.mode / hub.verify_token / hub.challenge
    $mode      = $request->query('hub_mode') ?? $request->query('hub.mode');
    $token     = $request->query('hub_verify_token') ?? $request->query('hub.verify_token');
    $challenge = $request->query('hub_challenge') ?? $request->query('hub.challenge');

    if ($mode !== 'subscribe' || !$token || !$challenge) {
        return response('Forbidden', 403);
    }

    // buscamos una cuenta que tenga ese verify_token
    $account = WhatsappAccount::where('verify_token', $token)->first();

    if ($account) {
        return response($challenge, 200);
    }

    return response('Forbidden', 403);
}


   public function receive(Request $request, WhatsappCloudMediaService $media)
{
    $payload = $request->all();

    Log::channel('single')->info(
        "WHATSAPP WEBHOOK RAW:\n" . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
    );

    try {
        $entry = $payload['entry'][0]['changes'][0]['value'] ?? null;
        if (!$entry) return response()->json(['ok' => true, 'note' => 'no entry.value']);

        $metadataPhoneId = $entry['metadata']['phone_number_id'] ?? null;
        if (!$metadataPhoneId) return response()->json(['ok' => true, 'note' => 'no phone_number_id']);

        $account = WhatsappAccount::where('phone_number_id', $metadataPhoneId)
            ->where('is_active', true)
            ->first();

        if (!$account) {
            Log::warning("WA: No account match for phone_number_id={$metadataPhoneId}");
            return response()->json(['ok' => true, 'note' => 'no account']);
        }

        $teamId = $account->team_id;

        // OJO: a veces el webhook llega como "statuses" sin "messages"
        $messages = $entry['messages'] ?? [];
        if (empty($messages)) {
            Log::info("WA: payload without messages (maybe statuses).", [
                'has_statuses' => !empty($entry['statuses'] ?? []),
            ]);
            return response()->json(['ok' => true, 'note' => 'no messages']);
        }

        $contactName = $entry['contacts'][0]['profile']['name'] ?? null;

        foreach ($messages as $m) {
            $from  = $m['from'] ?? null;
            $msgId = $m['id'] ?? null;
            $type  = $m['type'] ?? 'text';

            if (!$from || !$msgId) {
                Log::warning("WA: message missing from or id", ['m' => $m]);
                continue;
            }

            // Evitar duplicados
            if (WhatsappMessage::where('message_id', $msgId)->exists()) {
                continue;
            }

            // 1) Upsert conversación
            $conversation = WhatsappConversation::firstOrCreate(
                [
                    'team_id'             => $teamId,
                    'whatsapp_account_id' => $account->id,
                    'wa_id'               => $from,
                ],
                [
                    'contact_name'  => $contactName,
                    'contact_phone' => $from,
                    'status'        => 'open',
                ]
            );

            // ---------------------------
            // 2) Parse + Media handling
            // ---------------------------
            $textBody    = null;
            $mediaId     = null;
            $mimeType    = null;
            $fileSize    = null;
            $filename    = null;
            $caption     = null;
            $storagePath = null;
            $publicUrl   = null;

            if ($type === 'text') {
                $textBody = $m['text']['body'] ?? '';
            } else {
                // WhatsApp Cloud API manda ID de media según tipo
                if ($type === 'image') {
                    $mediaId = $m['image']['id'] ?? null;
                    $caption = $m['image']['caption'] ?? null;
                } elseif ($type === 'audio') {
                    $mediaId = $m['audio']['id'] ?? null;
                } elseif ($type === 'document') {
                    $mediaId  = $m['document']['id'] ?? null;
                    $filename = $m['document']['filename'] ?? null;
                    $caption  = $m['document']['caption'] ?? null;
                } elseif ($type === 'video') {
                    $mediaId = $m['video']['id'] ?? null;
                    $caption = $m['video']['caption'] ?? null;
                } else {
                    // otros tipos: sticker, location, contacts, etc.
                    $textBody = "[{$type}]";
                }

                // Si hay media_id => obtener URL temporal y descargar binario
                if ($mediaId) {
                    try {
                        $info = $media->getMediaInfo($account, $mediaId);
                        $mimeType    = $info['mime_type'] ?? null;
                        $fileSize    = $info['file_size'] ?? null;
                        $downloadUrl = $info['url'] ?? null;

                        if ($downloadUrl) {
                            $bin = $media->downloadMediaBinary($account, $downloadUrl);

                            $saved = $media->storeMedia(
                                $teamId,
                                $conversation->id,
                                $bin,
                                $mimeType ?? 'application/octet-stream',
                                $filename
                            );

                            $storagePath = $saved['storage_path'] ?? null;
                            $publicUrl   = $saved['public_url'] ?? null;
                            $filename    = $saved['filename'] ?? $filename;
                        }

                        $textBody = match ($type) {
                            'image'    => '[imagen]',
                            'audio'    => '[audio]',
                            'video'    => '[video]',
                            'document' => '[archivo]',
                            default    => "[{$type}]",
                        };
                    } catch (\Throwable $e) {
                        Log::warning("WA media download failed: ".$e->getMessage(), [
                            'media_id' => $mediaId,
                            'type' => $type,
                        ]);
                        $textBody = "[{$type}]";
                    }
                }
            }

            // actualizar datos visibles de la conversación
            $conversation->update([
                'contact_name'         => $contactName ?? $conversation->contact_name,
                'contact_phone'        => $from ?? $conversation->contact_phone,
                'last_message_at'      => now(),
                'last_message_preview' => mb_substr(($caption ?: $textBody) ?? '', 0, 180),
            ]);

            // 3) Guardar mensaje
            $message = WhatsappMessage::create([
                'team_id'                  => $teamId,
                'whatsapp_conversation_id'  => $conversation->id,
                'direction'                => 'inbound',
                'message_id'               => $msgId,
                'type'                     => $type,
                'body'                     => $textBody,
                'raw_payload'              => json_encode($m, JSON_UNESCAPED_UNICODE),

                // media fields (si ya los agregaste en tu tabla)
                'media_id'                 => $mediaId,
                'mime_type'                => $mimeType,
                'file_size'                => $fileSize,
                'filename'                 => $filename,
                'caption'                  => $caption,
                'storage_path'             => $storagePath,
                'public_url'               => $publicUrl,

                // si tu tabla NO tiene este campo, elimínalo
                'sent_by_user_id'          => null,
            ]);

            // 4) Broadcast realtime (no fatal si Reverb no está disponible)
            try {
                event(new \App\Events\WhatsappMessageReceived($message));
            } catch (\Throwable $broadcastErr) {
                Log::warning('WA broadcast failed (non-fatal): ' . $broadcastErr->getMessage());
            }

            // 5) Enlazar / crear deal
            $this->attachOrCreateDeal($account, $conversation, $contactName);

            // 6) AI auto-reply (solo mensajes de texto entrantes)
            if ($type === 'text') {
                $this->dispatchAiReply($account, $conversation, $message);
            }
        }

        return response()->json(['ok' => true]);
    } catch (\Throwable $e) {
        Log::error("WA webhook error: {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
        Log::error("WA payload when error:\n" . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        return response()->json(['ok' => true]);
    }
}

   /* protected function attachOrCreateDeal(WhatsappAccount $account, WhatsappConversation $conversation, ?string $contactName): void
    {
        $pipelineId = $account->pipeline_id;

        // buscamos último deal enlazado
        $currentDeal = $conversation->deals()
            ->orderByDesc('whatsapp_conversation_deals.created_at')
            ->first();

        // Si existe y está open => seguimos usando ese
        if ($currentDeal && $currentDeal->status === 'open') {
            return;
        }

        // Si no existe o está won/lost => crear uno nuevo
        $firstStage = PipelineStage::where('pipeline_id', $pipelineId)->orderBy('sort_order')->first();
        if (!$firstStage) return;

        $title = ($contactName ? $contactName : ($conversation->contact_phone ?? 'WhatsApp')).' - WhatsApp';

        $deal = Deal::create([
            'team_id' => $account->team_id,
            'owner_id' => $account->team->owner_id ?? 1, // o Auth system user
            'pipeline_id' => $pipelineId,
            'stage_id' => $firstStage->id,
            'contact_id' => null, // luego lo conectas si creas contacto
            'responsible_id' => null,
            'title' => $title,
            'amount' => null,
            'currency' => 'PEN',
            'status' => 'open',
            'close_date' => null,
            'description' => 'Conversación WhatsApp iniciada automáticamente.',
        ]);

        $conversation->deals()->attach($deal->id, [
            'started_at' => now(),
            'ended_at' => null,
        ]);
    } */

     protected function attachOrCreateDeal(
    WhatsappAccount $account,
    WhatsappConversation $conversation,
    ?string $contactName
): void {
    $pipelineId = $account->pipeline_id;
    $teamId     = $account->team_id;

    // CLAVE del cliente (lo más estable es wa_id)
    $waId = $conversation->wa_id ?? $conversation->contact_phone;

    if (!$waId) {
        // sin identificador no podemos evitar duplicados
        return;
    }

    // 1) Si esta conversación ya tiene un deal OPEN enlazado -> nada
    $alreadyOpenLinked = $conversation->deals()
        ->where('deals.status', 'open')
        ->exists();

    if ($alreadyOpenLinked) {
        return;
    }

    // 2) Buscar SI YA EXISTE un deal OPEN para ese waId (cliente) en el pipeline
    $openDeal = Deal::query()
        ->where('team_id', $teamId)
        ->where('pipeline_id', $pipelineId)
        ->where('status', 'open')
        ->where('wa_id', $waId)
        ->orderByDesc('id')
        ->first();

    if ($openDeal) {
        // 2.1) Enlazarlo a esta conversación si no está enlazado
        $alreadyLinked = $conversation->deals()
            ->where('deals.id', $openDeal->id)
            ->exists();

        if (!$alreadyLinked) {
            $conversation->deals()->attach($openDeal->id, [
                'started_at' => now(),
                'ended_at'   => null,
            ]);
        }

        return; // IMPORTANTE: no crear otro
    }

    // 3) No existe deal OPEN para ese waId -> crear nuevo
    $firstStage = PipelineStage::where('pipeline_id', $pipelineId)
        ->orderBy('sort_order')
        ->first();

    if (!$firstStage) return;

    $title = ($contactName ?: ($conversation->contact_phone ?? 'WhatsApp')) . ' - WhatsApp';

    $deal = Deal::create([
        'team_id'        => $teamId,
        'owner_id'       => $account->team->owner_id ?? 1,
        'pipeline_id'    => $pipelineId,
        'stage_id'       => $firstStage->id,

        // si ya tienes contactos, aquí puedes poner el contact_id real
        'contact_id'     => null,

        // CLAVE PARA EVITAR DUPLICADOS
        'wa_id'          => $waId,

        'responsible_id' => null,
        'title'          => $title,
        'amount'         => null,
        'currency'       => 'PEN',
        'status'         => 'open',
        'close_date'     => null,
        'description'    => 'Conversación WhatsApp iniciada automáticamente.',
    ]);

    $conversation->deals()->attach($deal->id, [
        'started_at' => now(),
        'ended_at'   => null,
    ]);
}
   

    protected function dispatchAiReply(
        WhatsappAccount $account,
        WhatsappConversation $conversation,
        WhatsappMessage $message
    ): void {
        $assistant = \App\Models\WhatsappAiAssistant::where('whatsapp_account_id', $account->id)
            ->where('is_active', true)
            ->first();

        if (!$assistant) return;

        \App\Jobs\ProcessWhatsappAiReply::dispatch($conversation->id, $assistant->id)
            ->delay(now()->addSeconds(2)); // pequeño delay para que el mensaje ya esté guardado
    }

    protected function writeToLog1($data, $title = 'DEBUG')
    {
        Log::channel('single')->info($title, [
            'data' => $data
        ]);
    }
}
