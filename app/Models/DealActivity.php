<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DealActivity extends Model
{
    use HasFactory;

    /** Horas de gracia para marcar completada tras el vencimiento. */
    public const GRACE_HOURS = 3;

    protected $fillable = [
        'deal_id',
        'user_id',
        'type',
        'subject',
        'due_at',
        'status',
        'notes',
        'reminded_at',
        'notify_minutes',
        'reminded_minutes',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'reminded_at' => 'datetime',
        'notify_minutes' => 'array',
        'reminded_minutes' => 'array',
    ];

    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * ¿Se puede marcar como completada?
     * - Si ya está completada: no.
     * - Admin del equipo: siempre.
     * - Sin fecha límite: siempre.
     * - Con fecha: hasta GRACE_HOURS después del vencimiento.
     */
    public function completableBy(?\App\Models\User $user, ?\App\Models\Team $team = null): bool
    {
        if ($this->status === 'done') {
            return false;
        }
        if ($user && $team && $user->isCrmAdminFor($team)) {
            return true;
        }
        if (!$this->due_at) {
            return true;
        }
        return now()->lte($this->due_at->copy()->addHours(self::GRACE_HOURS));
    }
}
