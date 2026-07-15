<?php

use App\Models\Worker;
use Illuminate\Support\Facades\Broadcast;

// Participants are keyed "type-id" (e.g. "user-3", "worker-9") to match
// MessageSent::broadcastOn() — users and workers are separate tables with
// independent id sequences, so bare ids would let unrelated conversations
// collide onto the same channel name.
// No type-hint on $user: chat participants are either a User or a Worker,
// and Worker doesn't extend Authenticatable, so there's no common class to hint.
Broadcast::channel('chat.{participant1}.{participant2}', function ($user, $participant1, $participant2) {
    $selfKey = ($user instanceof Worker ? 'worker' : 'user') . '-' . $user->id;
    return $selfKey === $participant1 || $selfKey === $participant2;
});
