<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuickReply extends Model
{
    protected $fillable = [
        'team_id', 'user_id', 'shortcut', 'title', 'content',
        'is_team_wide', 'times_used',
    ];

    protected $casts = [
        'is_team_wide' => 'boolean',
        'times_used'   => 'integer',
    ];

    public function team() { return $this->belongsTo(Team::class); }
    public function user() { return $this->belongsTo(User::class); }
}
