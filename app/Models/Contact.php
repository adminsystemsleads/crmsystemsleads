<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'owner_id',
        'first_name',
        'last_name',
        'name',
        'email',
        'phone',
        'company',
        'position',
        'status',
        'source',
        'notes',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function deals()
    {
        return $this->hasMany(Deal::class);
    }

    // Valores de campos personalizados
    public function customFieldValues()
    {
        return $this->morphMany(CustomFieldValue::class, 'valuable');
    }
}
