<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class OpenAiService
{
    private const API_URL = 'https://api.openai.com/v1/chat/completions';

    /**
     * Chat sin function calling — devuelve string del reply.
     *
     * @param  array<array{role:string,content:string}>  $messages
     */
    public function chat(
        string $apiKey,
        string $model,
        array $messages,
        float $temperature = 0.7,
        int $maxTokens = 500
    ): string {
        $res = Http::withToken($apiKey)
            ->timeout(30)
            ->acceptJson()
            ->post(self::API_URL, [
                'model'       => $model,
                'messages'    => $messages,
                'temperature' => $temperature,
                'max_tokens'  => $maxTokens,
            ]);

        if (!$res->successful()) {
            throw new \RuntimeException('OpenAI API error: ' . $res->body());
        }

        $content = $res->json('choices.0.message.content');

        if (!is_string($content) || trim($content) === '') {
            throw new \RuntimeException('OpenAI returned empty response.');
        }

        return trim($content);
    }

    /**
     * Chat con tools (function calling). Devuelve el message completo
     * para que el caller decida si ejecuta tool_calls o devuelve texto.
     *
     * @return array{role:string,content?:string|null,tool_calls?:array}
     */
    public function chatWithTools(
        string $apiKey,
        string $model,
        array $messages,
        array $tools,
        float $temperature = 0.7,
        int $maxTokens = 500
    ): array {
        $payload = [
            'model'       => $model,
            'messages'    => $messages,
            'temperature' => $temperature,
            'max_tokens'  => $maxTokens,
        ];
        if (!empty($tools)) {
            $payload['tools']       = $tools;
            $payload['tool_choice'] = 'auto';
        }

        $res = Http::withToken($apiKey)
            ->timeout(45)
            ->acceptJson()
            ->post(self::API_URL, $payload);

        if (!$res->successful()) {
            throw new \RuntimeException('OpenAI API error: ' . $res->body());
        }

        $message = $res->json('choices.0.message');
        if (!is_array($message)) {
            throw new \RuntimeException('OpenAI returned no message.');
        }

        return $message;
    }

    public static function availableModels(): array
    {
        return [
            'gpt-4o'           => 'GPT-4o (recomendado)',
            'gpt-4o-mini'      => 'GPT-4o Mini (rápido y económico)',
            'gpt-4-turbo'      => 'GPT-4 Turbo',
            'gpt-4'            => 'GPT-4',
            'gpt-3.5-turbo'    => 'GPT-3.5 Turbo (más económico)',
        ];
    }
}
