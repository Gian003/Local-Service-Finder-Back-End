<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function getConversation(Request $request, $workerId)
    {
        $userId = $request->user()->id;

        $messages = Message::where(function ($query) use ($userId, $workerId) {
            $query->where('sender_id', $userId)
                ->where('receiver_id', $workerId);
        })
            ->orWhere(function ($query) use ($userId, $workerId) {
                $query->where('sender_id', $workerId)
                    ->where('receiver_id', $userId);
            })
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get();

        Message::where('sender_id', $workerId)
            ->where('receiver_id', $userId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => Carbon::now()
            ]);

        return response()->json($messages);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|integer',
            'content' => 'required|string|max:1000'
        ]);

        $message = Message::create([
            'sender_id' => $request->user()->id,
            'receiver_id' => $request->input('receiver_id'),
            'content' => $request->input('content'),
        ]);

        $message->load('sender');

        broadcast(new MessageSent($message))->toOthers();

        return response()->json($message, 201);
    }

    public function getConversationList(Request $request)
    {
        $userId = $request->user()->id;

        $conversations = Message::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function ($message) use ($userId) {
                return $message->sender_id === $userId
                    ? 'worker_' . $message->receiver_id
                    : 'user_' . $message->sender_id;
            })
            ->map(fn($messages) => $messages->first())
            ->values();

        return response()->json($conversations);
    }

    public function markAsRead(Request $request, $messageId)
    {
        $message = Message::find($messageId);

        if ($message && $message->receiver_id === $request->user()->id) {
            $message->update([
                'is_read' => true,
                'read_at' => Carbon::now()
            ]);
        }

        return response()->json(['message' => 'Marked as read']);
    }
}
