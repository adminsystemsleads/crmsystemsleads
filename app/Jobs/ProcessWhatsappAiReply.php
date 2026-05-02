<?php

namespace App\Jobs;

use App\Models\WhatsappAiAssistant;
use App\Models\WhatsappConversation;
use App\Models\WhatsappMessage;
use App\Services\OpenAiService;
use App\Services\WhatsappCloudService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessWhatsappAiReply implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;
    public int $timeout = 60;

    public function __construct(
        public readonly int $conversationId,
        public readonly int $assistantId,
    ) {}

    public function handle(OpenAiService $openAi, WhatsappCloudService $wa): void
    {
        $conversation = WhatsappConversation::with('account')->find($this->conversationId);
        $assistant    = WhatsappAiAssistant::find($this->assistantId);

        if (!$conversation || !$assistant || !$assistant->is_active) {
            return;
        }

        $account = $conversation->account;
        if (!$account) return;

        // Build messages array from conversation history
        $history = WhatsappMessage::where('whatsapp_conversation_id', $this->conversationId)
            ->whereIn('type', ['text'])          // only text for context
            ->orderByDesc('id')
            ->limit($assistant->context_messages)
            ->get()
            ->reverse()
            ->values();

        $openAiMessages = [];

        if ($assistant->system_prompt) {
            $openAiMessages[] = ['role' => 'system', 'content' => $assistant->system_prompt];
        }

        foreach ($history as $msg) {
            $role    = $msg->direction === 'outbound' ? 'assistant' : 'user';
            $content = $msg->body ?? '';
            if ($content === '') continue;
            $openAiMessages[] = ['role' => $role, 'content' => $content];
        }

        if (empty($openAiMessages) || end($openAiMessages)['role'] !== 'user') {
            return; // nothing to reply to
        }

        try {
            $reply = $openAi->chat(
                $assistant->api_key,
                $assistant->model,
                $openAiMessages,
                $assistant->temperature,
                $assistant->max_tokens,
            );
        } catch (\Throwable $e) {
            Log::error('WhatsApp AI reply failed: ' . $e->getMessage(), [
                'conversation_id' => $this->conversationId,
                'assistant_id'    => $this->assistantId,
            ]);
            return;
        }

        // Send via WhatsApp Cloud API
        try {
            $res = $wa->sendText($account, $conversation->contact_phone, $reply);
        } catch (\Throwable $e) {
            Log::error('WhatsApp AI send failed: ' . $e->getMessage());
            return;
        }

        $metaMessageId = $res['messages'][0]['id'] ?? null;

        $message = WhatsappMessage::create([
            'team_id'                  => $conversation->team_id,
            'whatsapp_conversation_id' => $conversation->id,
            'direction'                => 'outbound',
            'message_id'               => $metaMessageId,
            'type'                     => 'text',
            'body'                     => $reply,
            'sent_by_user_id'          => null, // sent by AI
            'raw_payload'              => json_encode($res),
        ]);

        $conversation->update([
            'last_message_at'      => now(),
            'last_message_preview' => mb_substr($reply, 0, 180),
        ]);

        event(new \App\Events\WhatsappMessageReceived($message));
    }
}
