<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Form extends Model
{
    protected $fillable = [
        'team_id', 'name', 'slug',
        'title', 'subtitle', 'button_text', 'success_message', 'redirect_url',
        'bg_color', 'card_color', 'text_color', 'primary_color', 'button_text_color',
        'pipeline_id', 'stage_id', 'assigned_user_id', 'deal_title_template',
        'deal_dedup_mode', 'move_stage_id', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /** Genera un slug corto único para el link público. */
    public static function generateUniqueSlug(): string
    {
        do {
            $slug = Str::lower(Str::random(10));
        } while (static::where('slug', $slug)->exists());

        return $slug;
    }

    public function getPublicUrlAttribute(): string
    {
        return route('public.form.show', $this->slug);
    }

    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function pipeline()
    {
        return $this->belongsTo(Pipeline::class, 'pipeline_id');
    }

    public function stage()
    {
        return $this->belongsTo(PipelineStage::class, 'stage_id');
    }

    public function moveStage()
    {
        return $this->belongsTo(PipelineStage::class, 'move_stage_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function fields()
    {
        return $this->hasMany(FormField::class)->orderBy('sort_order');
    }

    public function submissions()
    {
        return $this->hasMany(FormSubmission::class)->latest();
    }
}
