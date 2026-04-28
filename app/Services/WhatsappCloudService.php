<?php

namespace App\Services;

use App\Models\WhatsappAccount;
use Illuminate\Support\Facades\Http;

class WhatsappCloudService
{
    public function sendText(WhatsappAccount $account, string $toPhone, string $text): array
    {
        $url = "https://graph.facebook.com/v20.0/{$account->phone_number_id}/messages";

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $toPhone,
            'type' => 'text',
            'text' => [
                'preview_url' => false,
                'body' => $text,
            ],
        ];

        $res = Http::withToken($account->access_token)
            ->acceptJson()
            ->post($url, $payload);

        if (!$res->successful()) {
            throw new \RuntimeException('WhatsApp send failed: '.$res->body());
        }

        return $res->json();
    }

    
}
