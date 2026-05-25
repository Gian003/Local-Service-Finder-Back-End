<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use SoftDeletes;

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
