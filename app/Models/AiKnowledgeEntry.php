<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiKnowledgeEntry extends Model
{
    protected $fillable = [
        'team_id',
        'whatsapp_ai_assistant_id',
        'source',
        'title',
        'original_filename',
        'mime_type',
        'size_bytes',
        'content',
        'storage_path',
        'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'size_bytes' => 'integer',
    ];

    public function assistant() { return $this->belongsTo(WhatsappAiAssistant::class, 'whatsapp_ai_assistant_id'); }
    public function team()      { return $this->belongsTo(Team::class); }

    public function getSizeKbAttribute(): float
    {
        return round($this->size_bytes / 1024, 1);
    }

    public function getCharCountAttribute(): int
    {
        return mb_strlen($this->content ?? '');
    }
}
