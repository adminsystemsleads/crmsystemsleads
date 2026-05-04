<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappAccount extends Model
{
    protected $fillable = [
        'team_id',
        'name',
        'phone_number_id',
        'waba_id',
        'business_id',   // ✅ OK si existe en DB
        'access_token',
        'verify_token',  // ✅ OK si existe en DB
        'pipeline_id',
        'is_active',
        'last_assigned_user_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function team()
    {
        return $this->belongsTo(\App\Models\Team::class, 'team_id');
    }

    public function pipeline()
    {
        return $this->belongsTo(Pipeline::class, 'pipeline_id');
    }

    public function conversations()
    {
        return $this->hasMany(WhatsappConversation::class, 'whatsapp_account_id');
    }

    public function aiAssistant()
    {
        return $this->hasOne(WhatsappAiAssistant::class, 'whatsapp_account_id');
    }

    /**
     * Usuarios que pueden recibir negociaciones de esta cuenta (round-robin equitativo).
     */
    public function assignees()
    {
        return $this->belongsToMany(\App\Models\User::class, 'whatsapp_account_user')
            ->withTimestamps()
            ->orderBy('whatsapp_account_user.id');
    }

    /**
     * Devuelve el siguiente usuario en la rotación equitativa y actualiza el cursor.
     * Retorna null si no hay assignees configurados.
     */
    public function nextAssigneeId(): ?int
    {
        $userIds = $this->assignees()->pluck('users.id')->all();
        if (empty($userIds)) {
            return null;
        }

        // Encontrar al siguiente después del último asignado
        $lastIndex = $this->last_assigned_user_id
            ? array_search($this->last_assigned_user_id, $userIds, true)
            : false;

        if ($lastIndex === false) {
            $nextId = $userIds[0];
        } else {
            $nextIndex = ($lastIndex + 1) % count($userIds);
            $nextId    = $userIds[$nextIndex];
        }

        $this->forceFill(['last_assigned_user_id' => $nextId])->saveQuietly();

        return (int) $nextId;
    }
}
