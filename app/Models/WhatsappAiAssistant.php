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
        'function_calling_enabled',
        'capture_config',
    ];

    protected $casts = [
        'is_active'                => 'boolean',
        'function_calling_enabled' => 'boolean',
        'api_key'                  => 'encrypted',
        'temperature'              => 'float',
        'max_tokens'               => 'integer',
        'context_messages'         => 'integer',
        'capture_config'           => 'array',
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
