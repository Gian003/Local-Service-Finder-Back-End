<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeocodingService
{
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.google.places_api_key');
    }

    public function getCoordinates(string $address): ?array
    {
        $response = Http::get(
            'https://maps.googleapis.com/maps/api/geocode/json',
            [
                'address' => $address,
                'key'     => $this->apiKey,
            ]
        );

        if ($response->failed()) return null;

        $results = $response->json('results');

        if (empty($results)) return null;

        $location = $results[0]['geometry']['location'];

        return [
            'latitude'  => $location['lat'],
            'longitude' => $location['lng'],
            'formatted' => $results[0]['formatted_address'],
        ];
    }
}
