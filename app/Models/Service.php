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
        'is_active',
        'review_count'
    ];

    public function getImageUrlAttribute($value): ?string
    {
        if (!$value) return null;

        if (str_starts_with($value, 'http')) {
            return $value;
        }

        return asset('storage/' . $value);
    }

    public function worker()
    {
        return $this->belongsTo(Worker::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
