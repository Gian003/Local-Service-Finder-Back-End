<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

// Guards non-idempotent write endpoints (create-a-row, toggle-a-flag) against
// the app's own http client, which auto-retries a request up to 3x on a
// timeout — a retry with the same idempotency_key would otherwise duplicate
// the side effect (or double-toggle a flag back to its original value).
class EnsureIdempotency
{
    private const TTL_MINUTES = 10;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->input('idempotency_key');

        if (!$key) {
            return $next($request);
        }

        $cacheKey = $this->cacheKey($request, $key);
        $cached = Cache::get($cacheKey);

        if ($cached) {
            return response()->json($cached['body'], $cached['status']);
        }

        $response = $next($request);

        // Only cache success — a genuine failure should still be retryable.
        if ($response->getStatusCode() < 300) {
            Cache::put($cacheKey, [
                'status' => $response->getStatusCode(),
                'body' => json_decode($response->getContent(), true),
            ], now()->addMinutes(self::TTL_MINUTES));
        }

        return $response;
    }

    private function cacheKey(Request $request, string $key): string
    {
        $actorId = optional($request->user())->id ?? 'guest';

        return "idempotency:{$actorId}:{$request->path()}:{$key}";
    }
}
