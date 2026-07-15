<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Message $message;

    /**
     * Create a new event instance.
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }


    public function broadcastOn(): Channel
    {
        // Qualify each participant with their type (user/worker), not just their
        // raw id — users and workers are separate tables with independent id
        // sequences, so two unrelated conversations could otherwise sort to the
        // same "chat.{id}.{id}" channel name.
        $participants = [
            $this->message->sender_type . '-' . $this->message->sender_id,
            $this->message->receiver_type . '-' . $this->message->receiver_id,
        ];
        sort($participants);

        return new PrivateChannel('chat.' . implode('.', $participants));
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'sender_id' => $this->message->sender_id,
            'sender_type' => $this->message->sender_type,
            'receiver_id' => $this->message->receiver_id,
            'receiver_type' => $this->message->receiver_type,
            'content' => $this->message->content,
            'is_read' => $this->message->is_read,
            'read_at' => $this->message->read_at,
            'created_at' => $this->message->created_at->toISOString(),
            'sender' => [
                'id' => $this->message->sender->id,
                'first_name' => $this->message->sender->first_name,
                'last_name' => $this->message->sender->last_name,
            ]
        ];
    }
}
