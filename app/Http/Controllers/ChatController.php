<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index()
    {
        $conversations = Auth::user()->conversations()->with('messages')->get();
        return view('chat.index', compact('conversations'));
    }

    public function show(Conversation $conversation)
    {
        if (!$conversation->users->contains(auth()->id())) {
            abort(404, 'Kamu tidak memiliki akses ke obrolan ini.');
        }
        $messages = $conversation->messages()->with('user')->get();
        return response()->json($messages);
    }

    public function storeMessage(Request $request, Conversation $conversation)
    {
        $request->validate([
            'body' => 'required|string',
        ]);

        $message = $conversation->messages()->create([
            'user_id' => auth()->id(),
            'body' => $request->body,
        ]);
        $message->load('user');
        return response()->json($message);
    }
}
