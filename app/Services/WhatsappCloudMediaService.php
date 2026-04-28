<?php

namespace App\Services;

use App\Models\WhatsappAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WhatsappCloudMediaService
{
    // 1) Obtener info del media (incluye URL temporal)
    public function getMediaInfo(WhatsappAccount $account, string $mediaId): array
    {
        $url = "https://graph.facebook.com/v20.0/{$mediaId}";

        $res = Http::withToken($account->access_token)
            ->acceptJson()
            ->get($url);

        if (!$res->ok()) {
            throw new \RuntimeException("MediaInfo error: ".$res->status()." ".$res->body());
        }

        return $res->json(); // { url, mime_type, sha256, file_size, id }
    }

    // 2) Descargar binario usando la URL y token
    public function downloadMediaBinary(WhatsappAccount $account, string $downloadUrl): string
    {
        $res = Http::withToken($account->access_token)
            ->withHeaders(['Accept' => '*/*'])
            ->get($downloadUrl);

        if (!$res->ok()) {
            throw new \RuntimeException("MediaDownload error: ".$res->status()." ".$res->body());
        }

        return $res->body();
    }

    // 3) Guardar en storage y devolver rutas
    public function storeMedia(
        int $teamId,
        int $conversationId,
        string $binary,
        string $mimeType,
        ?string $filename = null
    ): array {
        $ext = $this->guessExtension($mimeType, $filename);
        $safeName = $filename ? Str::slug(pathinfo($filename, PATHINFO_FILENAME)) : 'file';
        $finalName = $safeName.'-'.Str::random(8).'.'.$ext;

        $path = "whatsapp/{$teamId}/conversations/{$conversationId}/{$finalName}";
        Storage::disk('public')->put($path, $binary);

        return [
            'storage_path' => $path,
            'public_url'   => Storage::disk('public')->url($path),
            'filename'     => $filename ?: $finalName,
        ];
    }

    private function guessExtension(string $mimeType, ?string $filename): string
    {
        if ($filename && str_contains($filename, '.')) {
            return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        }

        return match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'audio/ogg'  => 'ogg',
            'audio/mpeg' => 'mp3',
            'audio/mp4'  => 'm4a',
            'video/mp4'  => 'mp4',
            'application/pdf' => 'pdf',
            default => 'bin',
        };
    }
}
