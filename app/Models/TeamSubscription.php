<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamSubscription extends Model
{
    protected $fillable = [
        'team_id', 'user_id', 'subscription_plan_id',
        'culqi_customer_id', 'culqi_card_id', 'culqi_subscription_id',
        'card_brand', 'card_last4',
        'status', 'current_period_start', 'current_period_end',
        'canceled_at', 'meta',
    ];

    protected $casts = [
        'current_period_start' => 'datetime',
        'current_period_end'   => 'datetime',
        'canceled_at'          => 'datetime',
        'meta'                 => 'array',
    ];

    public function team()    { return $this->belongsTo(Team::class); }
    public function user()    { return $this->belongsTo(User::class); }
    public function plan()    { return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id'); }
    public function payments(){ return $this->hasMany(Payment::class); }

    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'past_due'], true);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active'   => 'Activa',
            'past_due' => 'Pago vencido',
            'paused'   => 'Pausada',
            'canceled' => 'Cancelada',
            'failed'   => 'Fallida',
            'pending'  => 'Pendiente',
            default    => $this->status,
        };
    }
}
