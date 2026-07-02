<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormSubmission extends Model
{
    protected $fillable = [
        'form_id', 'team_id', 'contact_id', 'deal_id',
        'form_name', 'payload', 'ip', 'user_agent',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }
}
