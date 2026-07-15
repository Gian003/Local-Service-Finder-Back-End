<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeocodingService
{
    public function getCoordinates(string $address): ?array
    {
        // Skip if no Google API key configured
        $apiKey = config('services.google.places_api_key');
        if (!$apiKey) return null;

        try {
            $response = Http::get(
                'https://maps.googleapis.com/maps/api/geocode/json',
                [
                    'address' => $address,
                    'key'     => $apiKey,
                ]
            );

            if ($response->failed()) return null;

            $results = $response->json('results');
            if (empty($results)) return null;

            $location = $results[0]['geometry']['location'];
            return [
                'latitude'  => $location['lat'],
                'longitude' => $location['lng'],
            ];
        } catch (\Exception $e) {
            Log::warning('Geocoding failed: ' . $e->getMessage());
            return null;
        }
    }
}
