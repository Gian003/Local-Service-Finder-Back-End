<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'sender_id',
        'sender_type',
        'receiver_id',
        'receiver_type',
        'content',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    // sender_id/receiver_id may each reference either a User or a Worker
    // (independent id sequences), so a plain belongsTo can't tell them apart —
    // sender_type/receiver_type disambiguate via the morph map in AppServiceProvider.
    public function sender()
    {
        return $this->morphTo();
    }

    public function receiver()
    {
        return $this->morphTo();
    }

    public function getReceiverObject()
    {
        return $this->receiver ?? $this->sender;
    }
}
