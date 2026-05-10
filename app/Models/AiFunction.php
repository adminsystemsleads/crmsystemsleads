<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiFunction extends Model
{
    protected $fillable = [
        'team_id',
        'whatsapp_ai_assistant_id',
        'mode',
        'name',
        'description',
        'properties',
        'target_stage_id',
        'response_template',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'properties' => 'array',
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    public const MODE_UPDATE_CRM   = 'update_crm';
    public const MODE_CHANGE_STAGE = 'change_stage';
    public const MODE_INFO         = 'info';

    public function team()      { return $this->belongsTo(Team::class); }
    public function assistant() { return $this->belongsTo(WhatsappAiAssistant::class, 'whatsapp_ai_assistant_id'); }
    public function targetStage(){ return $this->belongsTo(PipelineStage::class, 'target_stage_id'); }
}
