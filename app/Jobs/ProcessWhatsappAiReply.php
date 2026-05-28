<?php

namespace App\Jobs;

use App\Models\WhatsappAiAssistant;
use App\Models\WhatsappConversation;
use App\Models\WhatsappMessage;
use App\Services\AiFunctionCallingService;
use App\Services\AiKnowledgeService;
use App\Services\OpenAiService;
use App\Services\WhatsappCloudService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessWhatsappAiReply implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;       // sin reintentos para no duplicar respuestas
    public int $timeout = 120;

    public function __construct(
        public readonly int $conversationId,
        public readonly int $assistantId,
    ) {}

    public function handle(
        OpenAiService $openAi,
        WhatsappCloudService $wa,
        AiFunctionCallingService $fn,
        AiKnowledgeService $kb
    ): void {
        $conversation = WhatsappConversation::with('account')->find($this->conversationId);
        $assistant    = WhatsappAiAssistant::find($this->assistantId);

        if (!$conversation || !$assistant || !$assistant->is_active || !$conversation->ai_active) {
            return;
        }

        $account = $conversation->account;
        if (!$account) return;

        if (empty($assistant->api_key)) {
            Log::warning('AI assistant sin API key', ['assistant' => $assistant->id]);
            return;
        }

        // Construir historial — respetando el reset de contexto si existe
        $historyQuery = WhatsappMessage::where('whatsapp_conversation_id', $this->conversationId)
            ->whereIn('type', ['text']);

        if ($conversation->ai_context_from) {
            $historyQuery->where('created_at', '>=', $conversation->ai_context_from);
        }

        $history = $historyQuery
            ->orderByDesc('id')
            ->limit($assistant->context_messages)
            ->get()
            ->reverse()
            ->values();

        $openAiMessages = [];

        $sp = $assistant->system_prompt ?? '';

        if ($assistant->function_calling_enabled) {
            $sp .= "\n\nIMPORTANTE: Tienes herramientas/funciones disponibles. Llámalas SIEMPRE que detectes la situación descrita en cada una (cliente comparte datos, acepta reunión, etc.). Tras ejecutar las funciones, RESPONDE al cliente con un mensaje normal en español.";
        }

        $knowledgeBlock = $kb->buildPromptContext($assistant);
        if ($knowledgeBlock !== '') {
            $sp .= "\n\n" . $knowledgeBlock;
        }

        if (trim($sp) !== '') {
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

        // Decidir si usar function calling: solo si está activo Y hay funciones definidas
        $tools = $assistant->function_calling_enabled
            ? $fn->buildTools($assistant)
            : [];

        // Log de diagnóstico
        Log::info('AI start', [
            'conv'          => $conversation->id,
            'assistant'     => $assistant->id,
            'model'         => $assistant->model,
            'fc_enabled'    => (bool) $assistant->function_calling_enabled,
            'tools_count'   => count($tools),
            'tool_names'    => array_map(fn($t) => $t['function']['name'] ?? '?', $tools),
            'messages'      => count($openAiMessages),
        ]);

        try {
            if (!empty($tools)) {
                $reply = $this->chatWithFunctions($openAi, $fn, $assistant, $conversation, $openAiMessages, $tools);
            } else {
                $reply = $openAi->chat(
                    $assistant->api_key,
                    $assistant->model,
                    $openAiMessages,
                    $assistant->temperature,
                    $assistant->max_tokens
                );
            }
        } catch (\Throwable $e) {
            Log::error('AI reply failed: ' . $e->getMessage(), [
                'conversation_id' => $this->conversationId,
                'assistant_id'    => $this->assistantId,
                'trace'           => substr($e->getTraceAsString(), 0, 1000),
            ]);
            return;
        }

        if (!is_string($reply) || trim($reply) === '') {
            Log::info('AI no devolvió texto para enviar', ['conv' => $this->conversationId]);
            return;
        }

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

        // Broadcast realtime (no fatal si Reverb no está disponible)
        try {
            event(new \App\Events\WhatsappMessageReceived($message));
        } catch (\Throwable $broadcastErr) {
            Log::warning('AI broadcast failed (non-fatal): ' . $broadcastErr->getMessage());
        }
    }

    /**
     * Bucle de function calling. Ejecuta tools y le pide al modelo
     * que finalmente devuelva texto al cliente.
     */
    private function chatWithFunctions(
        OpenAiService $openAi,
        AiFunctionCallingService $fn,
        WhatsappAiAssistant $assistant,
        WhatsappConversation $conversation,
        array $messages,
        array $tools
    ): ?string {
        $maxIterations = 4;

        for ($iter = 0; $iter < $maxIterations; $iter++) {
            $msg = $openAi->chatWithTools(
                $assistant->api_key,
                $assistant->model,
                $messages,
                $tools,
                $assistant->temperature,
                $assistant->max_tokens
            );

            Log::info('AI chatWithTools respuesta', [
                'iter'           => $iter,
                'has_content'    => !empty($msg['content']),
                'content_preview'=> isset($msg['content']) ? mb_substr((string) $msg['content'], 0, 200) : null,
                'tool_calls'     => !empty($msg['tool_calls']),
                'tool_count'     => is_array($msg['tool_calls'] ?? null) ? count($msg['tool_calls']) : 0,
            ]);

            // Sanitizar el mensaje del asistente antes de re-enviarlo a OpenAI:
            // solo conservamos campos que la API acepta de vuelta.
            $assistantMsg = ['role' => 'assistant'];
            if (isset($msg['content']) && is_string($msg['content']) && trim($msg['content']) !== '') {
                $assistantMsg['content'] = $msg['content'];
            } else {
                $assistantMsg['content'] = null;
            }
            if (!empty($msg['tool_calls']) && is_array($msg['tool_calls'])) {
                $assistantMsg['tool_calls'] = array_map(function ($tc) {
                    return [
                        'id'       => $tc['id'] ?? null,
                        'type'     => 'function',
                        'function' => [
                            'name'      => $tc['function']['name']      ?? '',
                            'arguments' => $tc['function']['arguments'] ?? '{}',
                        ],
                    ];
                }, $msg['tool_calls']);
            }

            $messages[] = $assistantMsg;

            $toolCalls = $msg['tool_calls'] ?? null;

            // Sin tool_calls → ya hay respuesta final
            if (!is_array($toolCalls) || empty($toolCalls)) {
                $content = $msg['content'] ?? '';
                if (is_string($content) && trim($content) !== '') {
                    return trim($content);
                }
                // El modelo no respondió texto ni tools → forzar un texto pidiendo expreso
                Log::info('AI sin texto ni tool_calls, forzando respuesta final', ['iter' => $iter]);
                break;
            }

            // Ejecutar cada tool y agregar resultado
            foreach ($toolCalls as $tc) {
                $name     = $tc['function']['name']      ?? '';
                $argsJson = $tc['function']['arguments'] ?? '{}';
                $args     = json_decode($argsJson, true) ?: [];
                $callId   = $tc['id'] ?? null;

                if (!$callId) {
                    Log::warning('tool_call sin id', ['tc' => $tc]);
                    continue;
                }

                try {
                    $exec   = $fn->executeToolCall($name, $args, $conversation, $assistant->id);
                    $result = $exec['result'] ?? ['ok' => false];
                } catch (\Throwable $e) {
                    Log::error("Tool '{$name}' lanzó excepción: " . $e->getMessage());
                    $result = ['ok' => false, 'message' => 'Error interno'];
                    $exec   = ['result' => $result];
                }

                Log::info('AI tool ejecutado', ['name' => $name, 'args' => $args, 'result' => $result]);

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

        // Si llegamos aquí sin texto final, hacer una última llamada SIN tools
        // para forzar al modelo a generar una respuesta de texto.
        Log::info('Forzando respuesta final SIN tools', ['conv' => $conversation->id]);

        $messages[] = [
            'role'    => 'system',
            'content' => 'Responde ahora al cliente con un mensaje normal en español. No llames a más funciones.',
        ];

        try {
            return $openAi->chat(
                $assistant->api_key,
                $assistant->model,
                $messages,
                $assistant->temperature,
                $assistant->max_tokens
            );
        } catch (\Throwable $e) {
            Log::error('AI final reply failed: ' . $e->getMessage());
            return null;
        }
    }
}
