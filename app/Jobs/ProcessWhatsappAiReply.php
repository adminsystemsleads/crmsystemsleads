<?php

namespace App\Jobs;

use App\Models\WhatsappAiAssistant;
use App\Models\WhatsappConversation;
use App\Models\WhatsappMessage;
use App\Services\AiFunctionCallingService;
use App\Services\OpenAiService;
use App\Services\WhatsappCloudService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessWhatsappAiReply implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;
    public int $timeout = 90;

    public function __construct(
        public readonly int $conversationId,
        public readonly int $assistantId,
    ) {}

    public function handle(
        OpenAiService $openAi,
        WhatsappCloudService $wa,
        AiFunctionCallingService $fn
    ): void {
        $conversation = WhatsappConversation::with('account')->find($this->conversationId);
        $assistant    = WhatsappAiAssistant::find($this->assistantId);

        if (!$conversation || !$assistant || !$assistant->is_active || !$conversation->ai_active) {
            return;
        }

        $account = $conversation->account;
        if (!$account) return;

        // Build messages array from conversation history
        $history = WhatsappMessage::where('whatsapp_conversation_id', $this->conversationId)
            ->whereIn('type', ['text'])
            ->orderByDesc('id')
            ->limit($assistant->context_messages)
            ->get()
            ->reverse()
            ->values();

        $openAiMessages = [];

        if ($assistant->system_prompt) {
            $sp = $assistant->system_prompt;
            if ($assistant->function_calling_enabled) {
                $sp .= "\n\nIMPORTANTE: Tienes herramientas para guardar datos del cliente en el CRM. Úsalas SIEMPRE que el cliente comparta información concreta (nombre, empresa, RUC/DNI, presupuesto, fecha tentativa, datos del proyecto, etc.). Llama a la función adecuada antes o después de responder al cliente.";
            }
            $openAiMessages[] = ['role' => 'system', 'content' => $sp];
        }

        foreach ($history as $msg) {
            $role    = $msg->direction === 'outbound' ? 'assistant' : 'user';
            $content = $msg->body ?? '';
            if ($content === '') continue;
            $openAiMessages[] = ['role' => $role, 'content' => $content];
        }

        if (empty($openAiMessages) || end($openAiMessages)['role'] !== 'user') {
            return;
        }

        try {
            $reply = $assistant->function_calling_enabled
                ? $this->chatWithFunctions($openAi, $fn, $assistant, $conversation, $openAiMessages)
                : $openAi->chat($assistant->api_key, $assistant->model, $openAiMessages, $assistant->temperature, $assistant->max_tokens);
        } catch (\Throwable $e) {
            Log::error('WhatsApp AI reply failed: ' . $e->getMessage(), [
                'conversation_id' => $this->conversationId,
                'assistant_id'    => $this->assistantId,
            ]);
            return;
        }

        if (!is_string($reply) || trim($reply) === '') return;

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
            'sent_by_user_id'          => null,
            'raw_payload'              => json_encode($res),
        ]);

        $conversation->update([
            'last_message_at'      => now(),
            'last_message_preview' => mb_substr($reply, 0, 180),
        ]);

        event(new \App\Events\WhatsappMessageReceived($message));
    }

    /**
     * Chat con function calling: ejecuta tools en loop hasta que el modelo
     * devuelva texto final para enviar al cliente.
     */
    private function chatWithFunctions(
        OpenAiService $openAi,
        AiFunctionCallingService $fn,
        WhatsappAiAssistant $assistant,
        WhatsappConversation $conversation,
        array $messages
    ): ?string {
        $tools = $fn->buildTools($assistant);

        // Loop limitado para evitar bucles infinitos
        for ($iter = 0; $iter < 5; $iter++) {
            $msg = $openAi->chatWithTools(
                $assistant->api_key,
                $assistant->model,
                $messages,
                $tools,
                $assistant->temperature,
                $assistant->max_tokens
            );

            $messages[] = $msg; // agregar la respuesta del asistente

            $toolCalls = $msg['tool_calls'] ?? null;
            if (!is_array($toolCalls) || empty($toolCalls)) {
                // No hay tools que ejecutar → respuesta final
                $content = $msg['content'] ?? '';
                return is_string($content) ? trim($content) : null;
            }

            // Ejecutar cada tool y agregar resultado al contexto
            foreach ($toolCalls as $tc) {
                $name      = $tc['function']['name']      ?? '';
                $argsJson  = $tc['function']['arguments'] ?? '{}';
                $args      = json_decode($argsJson, true) ?: [];
                $callId    = $tc['id'] ?? null;

                $exec = $fn->executeToolCall($name, $args, $conversation);
                $result = $exec['result'] ?? ['ok' => false];

                Log::info("AI tool call ejecutado", ['name' => $name, 'args' => $args, 'result' => $result]);

                // Si la función tiene response_template, pasarlo al modelo como sugerencia
                $payload = $result;
                if (!empty($exec['response'])) {
                    $payload['suggested_response'] = $exec['response'];
                }

                $messages[] = [
                    'role'         => 'tool',
                    'tool_call_id' => $callId,
                    'content'      => json_encode($payload, JSON_UNESCAPED_UNICODE),
                ];
            }
        }

        Log::warning('AI function calling alcanzó iteraciones máximas', ['conv' => $conversation->id]);
        return null;
    }
}
