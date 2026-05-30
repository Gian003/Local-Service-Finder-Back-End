<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use App\Services\GeocodingService;
use App\Helpers\NotificationHelper;

class PaymentController extends Controller
{
    public function createPaymentIntent(Request $request)
    {
        $request->validate([
            'amount'     => 'required|integer', // in centavos
            'service_id' => 'required|integer',
        ]);

        Stripe::setApiKey(config('services.stripe.secret'));

        $paymentIntent = PaymentIntent::create([
            'amount'   => $request->amount, // e.g. 150000 = ₱1,500
            'currency' => 'php',
            'metadata' => [
                'service_id' => $request->service_id,
                'user_id'    => $request->user()->id,
            ],
        ]);

        return response()->json([
            'client_secret' => $paymentIntent->client_secret,
        ]);
    }

    public function confirmBooking(Request $request)
    {
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'service_id'        => 'required|integer',
            'worker_id'         => 'required|integer',
            'address_id'        => 'required|integer',
            'scheduled_at'      => 'required|string',
            'total_price'       => 'required|numeric',
            'payment_method'    => 'required|string',
            'payment_intent_id' => 'nullable|string',
        ]);

        $address = \App\Models\Address::find($request->address_id);

        $lat = $lng = null;
        if ($address) {
            $geo = (new GeocodingService())->getCoordinates($address->address . ', ' . $address->city);
            $lat = $geo['latitude'] ?? null;
            $lng = $geo['longitude'] ?? null;
        }

        $booking = Booking::create([
            'user_id'           => $request->user()->id,
            'service_id'        => $request->service_id,
            'worker_id'         => $request->worker_id,
            'address_id'        => $request->address_id,
            'scheduled_at'      => $request->scheduled_at,
            'total_price'       => $request->total_price,
            'payment_method'    => $request->payment_method,
            'payment_intent_id' => $request->payment_intent_id,
            'latitude'          => $lat,
            'longitude'         => $lng,
            'status'            => 'pending',
        ]);

        $booking->load('service');

        NotificationHelper::paymentSuccessful(
            userId: $request->user()->id,
            serviceName: $booking->service->title ?? 'Service',
            amount: $booking->total_price,
        );

        NotificationHelper::bookingConfirmed(
            userId: $request->user()->id,
            serviceName: $booking->service->title ?? 'Service',
        );

        return response()->json([
            'message' => 'Booking confirmed!',
            'booking' => $booking,
        ], 201);
    }
}
