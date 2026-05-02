<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class OpenAiService
{
    private const API_URL = 'https://api.openai.com/v1/chat/completions';

    /**
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
