<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Conversation;

Broadcast::channel('chat.{conversationId}', function ($user, $conversationId) {
    return $user->conversations->contains($conversationId);
});

// Presence channel untuk melacak siapa yang online
Broadcast::channel('online', function ($user) {
    return ['id' => $user->id, 'name' => $user->name];
});