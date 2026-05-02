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


    public function uploadMedia(WhatsappAccount $account, string $filePath, string $mimeType, string $filename): string
    {
        $url = "https://graph.facebook.com/v20.0/{$account->phone_number_id}/media";

        $res = Http::withToken($account->access_token)
            ->acceptJson()
            ->attach('file', file_get_contents($filePath), $filename, ['Content-Type' => $mimeType])
            ->post($url, ['messaging_product' => 'whatsapp', 'type' => $mimeType]);

        if (!$res->successful()) {
            throw new \RuntimeException('Media upload failed: '.$res->body());
        }

        return $res->json()['id'];
    }

    public function sendMedia(WhatsappAccount $account, string $toPhone, string $type, string $mediaId, ?string $caption = null): array
    {
        $url = "https://graph.facebook.com/v20.0/{$account->phone_number_id}/messages";

        $mediaPayload = ['id' => $mediaId];
        if ($caption && in_array($type, ['image', 'video', 'document'])) {
            $mediaPayload['caption'] = $caption;
        }

        $res = Http::withToken($account->access_token)
            ->acceptJson()
            ->post($url, [
                'messaging_product' => 'whatsapp',
                'to'                => $toPhone,
                'type'              => $type,
                $type               => $mediaPayload,
            ]);

        if (!$res->successful()) {
            throw new \RuntimeException('WhatsApp send media failed: '.$res->body());
        }

        return $res->json();
    }
}
