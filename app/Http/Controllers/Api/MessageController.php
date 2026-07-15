<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Worker;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    // Chats are always between a User and a Worker, so the authenticated
    // principal's type determines the other party's type too.
    private function actorType(Request $request): string
    {
        return $request->user() instanceof Worker ? 'worker' : 'user';
    }

    public function getConversation(Request $request, $workerId)
    {
        $userId = $request->user()->id;
        $userType = $this->actorType($request);
        $otherType = $userType === 'worker' ? 'user' : 'worker';

        $messages = Message::where(function ($query) use ($userId, $userType, $workerId, $otherType) {
            $query->where('sender_id', $userId)
                ->where('sender_type', $userType)
                ->where('receiver_id', $workerId)
                ->where('receiver_type', $otherType);
        })
            ->orWhere(function ($query) use ($userId, $userType, $workerId, $otherType) {
                $query->where('sender_id', $workerId)
                    ->where('sender_type', $otherType)
                    ->where('receiver_id', $userId)
                    ->where('receiver_type', $userType);
            })
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get();

        Message::where('sender_id', $workerId)
            ->where('sender_type', $otherType)
            ->where('receiver_id', $userId)
            ->where('receiver_type', $userType)
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

        $senderType = $this->actorType($request);
        $receiverType = $senderType === 'worker' ? 'user' : 'worker';

        $message = Message::create([
            'sender_id' => $request->user()->id,
            'sender_type' => $senderType,
            'receiver_id' => $request->input('receiver_id'),
            'receiver_type' => $receiverType,
            'content' => $request->input('content'),
        ]);

        $message->load('sender');

        broadcast(new MessageSent($message))->toOthers();

        return response()->json($message, 201);
    }

    public function getConversationList(Request $request)
    {
        $userId = $request->user()->id;
        $userType = $this->actorType($request);

        $conversations = Message::where(function ($query) use ($userId, $userType) {
            $query->where('sender_id', $userId)->where('sender_type', $userType);
        })
            ->orWhere(function ($query) use ($userId, $userType) {
                $query->where('receiver_id', $userId)->where('receiver_type', $userType);
            })
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function ($message) use ($userId, $userType) {
                $isSender = $message->sender_id === $userId && $message->sender_type === $userType;
                return $isSender
                    ? $message->receiver_type . '_' . $message->receiver_id
                    : $message->sender_type . '_' . $message->sender_id;
            })
            ->map(fn($messages) => $messages->first())
            ->values();

        return response()->json($conversations);
    }

    public function markAsRead(Request $request, $messageId)
    {
        $userType = $this->actorType($request);
        $message = Message::find($messageId);

        if ($message
            && $message->receiver_id === $request->user()->id
            && $message->receiver_type === $userType
        ) {
            $message->update([
                'is_read' => true,
                'read_at' => Carbon::now()
            ]);
        }

        return response()->json(['message' => 'Marked as read']);
    }
}
