<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deal extends Model
{
    use HasFactory;

    protected $casts = [
    'close_date' => 'date',
    // otros casts...
];

    protected $fillable = [
        'team_id',
        'owner_id',
        'contact_id',
        'responsible_id',
        'title',
        'amount',
        'currency',
        'pipeline_id',
        'stage_id',
        'wa_id',
        'status',
        'close_date',
        'description',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function pipeline()
    {
        return $this->belongsTo(Pipeline::class);
    }

    public function stage()
    {
        return $this->belongsTo(PipelineStage::class, 'stage_id');
    }

    public function customFieldValues()
    {
        return $this->morphMany(CustomFieldValue::class, 'valuable');
    }

    public function comments()
{
    return $this->hasMany(DealComment::class)->latest();
}

public function activities()
{
    return $this->hasMany(DealActivity::class)->orderBy('due_at');
}

public function responsible()
{
    return $this->belongsTo(User::class, 'responsible_id');
}

public function whatsappConversations()
{
    return $this->belongsToMany(
        \App\Models\WhatsappConversation::class,
        'whatsapp_conversation_deals',
        'deal_id',
        'whatsapp_conversation_id'
    )->withTimestamps();
}

}
