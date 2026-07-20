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
        'gallery_images',
        'video_url',
        'discount_percent',
        'is_active',
        'review_count'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getImageUrlAttribute($value): ?string
    {
        if (!$value) return null;

        if (str_starts_with($value, 'http')) {
            return $value;
        }

        return asset('storage/' . $value);
    }

    // Stored as a JSON-encoded array of storage-relative paths — encode/decode
    // manually here rather than an Eloquent array cast, so this accessor can
    // also expand each path to a full URL the same way image_url does.
    public function setGalleryImagesAttribute($value): void
    {
        $this->attributes['gallery_images'] = $value ? json_encode($value) : null;
    }

    public function getGalleryImagesAttribute($value): array
    {
        if (!$value) return [];

        $paths = json_decode($value, true) ?: [];

        return array_map(
            fn ($path) => str_starts_with($path, 'http') ? $path : asset('storage/' . $path),
            $paths
        );
    }

    public function getVideoUrlAttribute($value): ?string
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
