<?php

use App\Models\WhatsappConversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('whatsapp.conversation.{conversationId}', function ($user, $conversationId) {
    $conv = WhatsappConversation::find($conversationId);
    if (!$conv) return false;

    // Jetstream Teams: solo usuarios del mismo team
    return $user->currentTeam && $user->currentTeam->id === $conv->team_id;
});