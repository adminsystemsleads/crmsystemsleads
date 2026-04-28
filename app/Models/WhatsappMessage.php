<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappMessage extends Model
{
    protected $fillable = [
    'team_id',
    'whatsapp_account_id',
    'whatsapp_conversation_id',
    'direction',
    'message_id',
    'type',
    'body',
    'raw_payload',
    'payload',
    'sent_by_user_id',
    'sent_at',

    // MEDIA
    'media_id',
    'mime_type',
    'file_size',
    'filename',
    'caption',
    'storage_path',
    'public_url',
];

    protected $casts = [
        'raw_payload' => 'array',
    ];

    public function conversation()
    {
        return $this->belongsTo(WhatsappConversation::class, 'whatsapp_conversation_id');
    }

    public function sentBy()
    {
        return $this->belongsTo(User::class, 'sent_by_user_id');
    }
}
