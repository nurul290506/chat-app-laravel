<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Conversation;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Kode otorisasi agar hanya anggota room chat yang bisa membaca pesan
Broadcast::channel('chat.{conversationId}', function ($user, $conversationId) {
    return Conversation::find($conversationId)->users->contains($user->id);
});