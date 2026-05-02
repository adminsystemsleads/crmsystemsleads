<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappAiAssistant extends Model
{
    protected $fillable = [
        'team_id',
        'whatsapp_account_id',
        'provider',
        'model',
        'api_key',
        'system_prompt',
        'temperature',
        'max_tokens',
        'context_messages',
        'is_active',
    ];

    protected $casts = [
        'is_active'        => 'boolean',
        'api_key'          => 'encrypted',
        'temperature'      => 'float',
        'max_tokens'       => 'integer',
        'context_messages' => 'integer',
    ];

    public function account()
    {
        return $this->belongsTo(WhatsappAccount::class, 'whatsapp_account_id');
    }

    public function team()
    {
        return $this->belongsTo(\App\Models\Team::class, 'team_id');
    }
}
