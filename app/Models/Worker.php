<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Worker extends Model
{
    use HasApiTokens;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'profile_photo',
        'category',
        'description',
        'is_available',
        'rating',
        'review_count'
    ];

    public function getProfilePhotoAttribute($value): ?string
    {
        if (!$value) return null;

        //If already full URL, return as is
        if (str_starts_with($value, 'http')) {
            return $value;
        }

        //Convert storage path to URL
        return asset('storage/' . $value);
    }

    protected $hidden = ['password'];

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
