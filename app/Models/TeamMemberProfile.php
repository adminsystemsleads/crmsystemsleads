<?php

// app/Models/TeamMemberProfile.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamMemberProfile extends Model
{
    protected $fillable = [
        'team_id','user_id','perfil','unidad','correo','telefono','notas'
    ];

    public function team()
    {
        return $this->belongsTo(\App\Models\Team::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    // app/Models/User.php
public function teamMemberProfiles()
{
    return $this->hasMany(\App\Models\TeamMemberProfile::class);
}

// app/Models/Team.php
public function memberProfiles()
{
    return $this->hasMany(\App\Models\TeamMemberProfile::class);
}

public function gastos()
{
    return $this->hasMany(GastoMensual::class, 'team_member_profile_id');
}
}
