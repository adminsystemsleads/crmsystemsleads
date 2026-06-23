<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DealActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'deal_id',
        'user_id',
        'type',
        'subject',
        'due_at',
        'status',
        'notes',
        'reminded_at',
        'notify_before',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'reminded_at' => 'datetime',
        'notify_before' => 'integer',
    ];

    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
