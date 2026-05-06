<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatTag extends Model
{
    protected $fillable = ['team_id', 'name', 'color'];

    public function team() { return $this->belongsTo(Team::class); }

    public function conversations()
    {
        return $this->belongsToMany(
            WhatsappConversation::class,
            'whatsapp_conversation_tag',
            'chat_tag_id',
            'whatsapp_conversation_id'
        )->withTimestamps();
    }

    public static function colorClasses(string $color): array
    {
        // map → [chip-bg, chip-text, ring]
        return match ($color) {
            'red'    => ['bg-red-100',    'text-red-700',    'ring-red-200'],
            'orange' => ['bg-orange-100', 'text-orange-700', 'ring-orange-200'],
            'amber'  => ['bg-amber-100',  'text-amber-700',  'ring-amber-200'],
            'yellow' => ['bg-yellow-100', 'text-yellow-700', 'ring-yellow-200'],
            'green'  => ['bg-green-100',  'text-green-700',  'ring-green-200'],
            'teal'   => ['bg-teal-100',   'text-teal-700',   'ring-teal-200'],
            'blue'   => ['bg-blue-100',   'text-blue-700',   'ring-blue-200'],
            'purple' => ['bg-purple-100', 'text-purple-700', 'ring-purple-200'],
            'pink'   => ['bg-pink-100',   'text-pink-700',   'ring-pink-200'],
            'gray'   => ['bg-gray-100',   'text-gray-700',   'ring-gray-200'],
            default  => ['bg-indigo-100', 'text-indigo-700', 'ring-indigo-200'],
        };
    }
}
