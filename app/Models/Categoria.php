<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    use HasFactory;

    protected $fillable = ['team_id', 'nombre' , 'tipo'];

    public function gastos()
    {
        return $this->hasMany(GastoMensual::class, 'categoria_id');
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
