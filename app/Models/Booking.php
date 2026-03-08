<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'worker_id',
        'service_id',
        'start_time',
        'status',
        'scheduled_at',
        'total_price',
        'notes'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function worker()
    {
        return $this->belongsTo(Worker::class);
    }

    public function review()
    {
        return $this->hasMany(Review::class);
    }
}
