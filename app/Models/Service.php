<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'worker_id',
        'title',
        'description',
        'price',
        'category',
        'image_url',
        'discount_percent',
        'is_active'
    ];

    public function worker()
    {
        return $this->belongsTo(Worker::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
