<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat.{id2}.{id2}', function (User $user, $id1, $id2) {
    return $user->id === (int)$id1 || $user->id === (int)$id2;
});
