<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LicenseCode extends Model
{
    protected $fillable = [
        'code', 'type', 'duration_unit', 'duration_value',
        'label', 'max_uses', 'used_count', 'is_active',
        'redeemed_at', 'redeemed_by_team_id', 'redeemed_by_user_id', 'created_by',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'duration_value' => 'integer',
        'max_uses'       => 'integer',
        'used_count'     => 'integer',
        'redeemed_at'    => 'datetime',
    ];

    public function creator()      { return $this->belongsTo(User::class, 'created_by'); }
    public function redeemedTeam() { return $this->belongsTo(Team::class, 'redeemed_by_team_id'); }
    public function redeemedUser() { return $this->belongsTo(User::class, 'redeemed_by_user_id'); }

    /** ¿Sigue disponible para canjear? */
    public function getIsAvailableAttribute(): bool
    {
        return $this->is_active && $this->used_count < $this->max_uses;
    }

    /** Es un código de prueba (semanas) en lugar de licencia (meses). */
    public function getIsTrialAttribute(): bool
    {
        return $this->type === 'trial';
    }

    /** Etiqueta legible: "12 meses" / "2 semanas". */
    public function getDurationLabelAttribute(): string
    {
        $unit = $this->duration_unit === 'weeks'
            ? ($this->duration_value === 1 ? 'semana' : 'semanas')
            : ($this->duration_value === 1 ? 'mes' : 'meses');

        return "{$this->duration_value} {$unit}";
    }

    /** Genera un código único con formato SL-XXXX-XXXX-XXXX. */
    public static function generateUniqueCode(): string
    {
        do {
            $raw  = strtoupper(Str::random(12)); // 12 chars alfanuméricos
            $code = 'SL-' . implode('-', str_split($raw, 4));
        } while (static::where('code', $code)->exists());

        return $code;
    }
}
