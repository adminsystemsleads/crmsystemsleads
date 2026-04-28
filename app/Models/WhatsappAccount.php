<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappAccount extends Model
{
    protected $fillable = [
        'team_id',
        'name',
        'phone_number_id',
        'waba_id',
        'business_id',   // ✅ OK si existe en DB
        'access_token',
        'verify_token',  // ✅ OK si existe en DB
        'pipeline_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function team()
    {
        return $this->belongsTo(\Laravel\Jetstream\Team::class, 'team_id');
    }

    public function pipeline()
    {
        return $this->belongsTo(Pipeline::class, 'pipeline_id');
    }

    public function conversations()
    {
        return $this->hasMany(WhatsappConversation::class, 'whatsapp_account_id');
    }
}
