<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index()
    {
        // Ambil room chat yang diikuti user login
        $conversations = Auth::user()->conversations()->with(['messages', 'users'])->get();
        return view('chat', compact('conversations'));
    }

    public function getUsers()
    {
        // Ambil daftar user lain untuk diajak chat baru
        return response()->json(User::where('id', '!=', auth()->id())->get());
    }

    public function createConversation($userId)
    {
        // Cek apakah chat privat sudah ada
        $conversation = Conversation::where('is_group', false)
            ->whereHas('users', function($q) { $q->where('user_id', auth()->id()); })
            ->whereHas('users', function($q) use ($userId) { $q->where('user_id', $userId); })
            ->first();

        if (!$conversation) {
            $conversation = Conversation::create(['is_group' => false]);
            $conversation->users()->attach([auth()->id(), $userId]);
        }

        return redirect()->route('chat.index');
    }

    public function show(Conversation $conversation)
    {
        return response()->json($conversation->messages()->with('user')->get());
    }

    public function storeMessage(Request $request, Conversation $conversation)
    {
        $message = $conversation->messages()->create([
            'body' => $request->body,
            'user_id' => auth()->id(),
        ]);

        $message->load('user');
        broadcast(new \App\Events\MessageSent($message))->toOthers();

        return response()->json($message);
    }
}