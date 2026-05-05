<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'team_id', 'user_id', 'provider', 'charge_id', 'source_id',
        'amount_cents', 'currency', 'status', 'months',
        'email', 'description', 'response', 'error_message', 'paid_at',
    ];

    protected $casts = [
        'response'     => 'array',
        'paid_at'      => 'datetime',
        'amount_cents' => 'integer',
        'months'       => 'integer',
    ];

    public function team() { return $this->belongsTo(Team::class); }
    public function user() { return $this->belongsTo(User::class); }

    public function getAmountAttribute(): float
    {
        return $this->amount_cents / 100;
    }
}
