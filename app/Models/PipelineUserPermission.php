<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PipelineUserPermission extends Model
{
    protected $fillable = [
        'pipeline_id',
        'user_id',
        'can_view',
        'can_edit',
        'can_delete',
        'can_configure',
    ];

    public function pipeline()
    {
        return $this->belongsTo(Pipeline::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
