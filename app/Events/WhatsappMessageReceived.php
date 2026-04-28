<?php

namespace App\Events;

use App\Models\WhatsappMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow; // importante NOW
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class WhatsappMessageReceived implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public WhatsappMessage $message;

    public function __construct(WhatsappMessage $message)
    {
        // Asegura relaciones útiles para la vista
        $this->message = $message->loadMissing(['sentBy']);
    }

    public function broadcastOn(): Channel
    {
        // Canal por conversación
        return new PrivateChannel('whatsapp.conversation.'.$this->message->whatsapp_conversation_id);
    }

    public function broadcastAs(): string
    {
        return 'WhatsappMessageReceived';
    }

    public function broadcastWith(): array
{
    $this->message->loadMissing('sentBy');

    return [
        // IDs
        'id' => $this->message->id,                       // id BD
        'message_id' => $this->message->message_id,       // id WA (si existe)
        'conversation_id' => $this->message->whatsapp_conversation_id,

        // contenido
        'direction' => $this->message->direction,
        'type' => $this->message->type,
        'body' => $this->message->body,
        'caption' => $this->message->caption,

        // media
        'public_url' => $this->message->public_url,
        'mime_type' => $this->message->mime_type,
        'filename' => $this->message->filename,
        'file_size' => $this->message->file_size,
        'storage_path' => $this->message->storage_path,
        'media_id' => $this->message->media_id,

        // fechas / usuario
        'created_at' => $this->message->created_at?->toIso8601String(),
        'sent_by' => $this->message->sentBy ? [
            'id' => $this->message->sentBy->id,
            'name' => $this->message->sentBy->name,
        ] : null,
    ];
}

}
