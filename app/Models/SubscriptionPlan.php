<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'slug', 'name', 'description', 'amount_cents', 'currency',
        'interval', 'interval_count', 'trial_days',
        'culqi_plan_id', 'is_active', 'features',
    ];

    protected $casts = [
        'amount_cents'   => 'integer',
        'interval_count' => 'integer',
        'trial_days'     => 'integer',
        'is_active'      => 'boolean',
        'features'       => 'array',
    ];

    public function subscriptions()
    {
        return $this->hasMany(TeamSubscription::class);
    }

    public function getAmountAttribute(): float
    {
        return $this->amount_cents / 100;
    }

    public function getIntervalLabelAttribute(): string
    {
        $count = $this->interval_count;
        return match (mb_strtolower($this->interval)) {
            'meses', 'months' => $count === 1 ? 'mes' : "{$count} meses",
            'años', 'years'   => $count === 1 ? 'año' : "{$count} años",
            'días', 'days'    => $count === 1 ? 'día' : "{$count} días",
            default           => "{$count} {$this->interval}",
        };
    }
}
