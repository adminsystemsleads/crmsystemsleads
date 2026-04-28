<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappConversation extends Model
{
    protected $fillable = [
  'team_id',
  'whatsapp_account_id',
  'wa_id',
  'contact_name',
  'contact_phone',
  'status',
  'last_message_at',
  'last_message_preview',
];


    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function account()
    {
        return $this->belongsTo(WhatsappAccount::class, 'whatsapp_account_id');
    }

    public function messages()
    {
        return $this->hasMany(WhatsappMessage::class, 'whatsapp_conversation_id')->orderBy('created_at');
    }

   public function deals()
{
    return $this->belongsToMany(Deal::class, 'whatsapp_conversation_deals', 'whatsapp_conversation_id', 'deal_id')
        ->withPivot(['started_at', 'ended_at'])
        ->withTimestamps();
}


    public function latestDeal()
    {
        // El último deal enlazado (por pivot.created_at)
        return $this->belongsToMany(Deal::class, 'whatsapp_conversation_deals', 'whatsapp_conversation_id', 'deal_id')
            ->withPivot(['started_at', 'ended_at'])
            ->orderByDesc('whatsapp_conversation_deals.created_at')
            ->limit(1);
    }
}
