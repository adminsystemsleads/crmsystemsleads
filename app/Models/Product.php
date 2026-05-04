<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'team_id', 'name', 'description', 'image_path', 'unit', 'price', 'currency', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price'     => 'float',
    ];

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) return null;
        return \Illuminate\Support\Facades\Storage::url($this->image_path);
    }

    public function team()
    {
        return $this->belongsTo(\App\Models\Team::class, 'team_id');
    }

    public function dealProducts()
    {
        return $this->hasMany(DealProduct::class);
    }
}
