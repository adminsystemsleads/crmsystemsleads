<?php

// app/Models/TeamLicense.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class TeamLicense extends Model
{
    protected $fillable = [
        'team_id','license_key',
        'trial_starts_at','trial_ends_at',
        'active_from','active_until',
        'is_active','meta'
    ];
    protected $casts = [
        'trial_starts_at'=>'datetime',
        'trial_ends_at'=>'datetime',
        'active_from'=>'datetime',
        'active_until'=>'datetime',
        'is_active'=>'boolean',
        'meta'=>'array',
    ];

    public function team() { return $this->belongsTo(Team::class); }

    public function getInTrialAttribute(): bool {
        return $this->trial_starts_at && $this->trial_ends_at && now()->between($this->trial_starts_at, $this->trial_ends_at);
    }

    public function getHasPaidAttribute(): bool {
        return $this->active_from && $this->active_until && now()->between($this->active_from, $this->active_until);
    }

    public function getIsExpiredAttribute(): bool {
        // Expirado si no está en trial ni tiene pago activo
        return ! $this->in_trial && ! $this->has_paid;
    }

    public function getExpiresAtAttribute(): ?Carbon {
        // fecha efectiva: la más “lejana” entre trial_end y paid_until si existen
        $candidates = array_filter([$this->trial_ends_at, $this->active_until]);
        if (empty($candidates)) return null;
        return collect($candidates)->max();
    }

    public function getRemainingDaysAttribute(): ?int {
        $exp = $this->expires_at;
        return $exp ? now()->diffInDays($exp, false) : null;
    }
}
