<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GastoMensual extends Model
{
    use HasFactory;

    protected $table = 'gasto_mensuales';

    protected $fillable = [
        'user_id',
        'team_id',
        'team_member_profile_id', // <- correcto
        'categoria_id',
        'mes',
        'año',                    // <- así está en BD
        'codigopago',
        'dia_pago',
        'link_vaucher',
        'monto_pagar',
        'pago_verificado',
        'descripcion',
    ];

    // Relaciones
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    // ÚNICA relación con el perfil de unidad
    public function memberProfile()
    {
        return $this->belongsTo(\App\Models\TeamMemberProfile::class, 'team_member_profile_id');
    }

    // --- Opcional pero MUY útil: alias "anio" para evitar la tilde en Blade ---
    protected $appends = ['anio'];

    public function getAnioAttribute()
    {
        return $this->attributes['año'] ?? null;
    }

    public function setAnioAttribute($value)
    {
        $this->attributes['año'] = $value;
    }
}
