<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Booking;
use App\Models\Service;
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
            'service_id' => 'required|integer',
            'demo_card'  => 'nullable|boolean',
        ]);

        $service = Service::find($request->service_id);

        if (!$service) {
            return response()->json(['message' => 'Service not found'], 422);
        }

        // The charge amount is derived from the Service price, never trusted
        // from the client — otherwise a tampered client could charge any
        // amount it likes for a given booking.
        $amountInCentavos = (int) round(((float) $service->price) * 100);

        Stripe::setApiKey(config('services.stripe.secret'));

        $params = [
            'amount'   => $amountInCentavos,
            'currency' => 'php',
            'metadata' => [
                'service_id' => $service->id,
                'user_id'    => $request->user()->id,
            ],
        ];

        // Demo path for presentations: confirm immediately with Stripe's
        // built-in test payment method token. This only ever works against
        // a test-mode secret key — Stripe rejects it outright on a live key
        // — so it can't be used to skip a real charge in production. It
        // still produces a genuine 'succeeded' PaymentIntent that
        // confirmBooking() verifies exactly like a real card payment, so
        // the credit-card flow is demoed end-to-end without Stripe's UI.
        if ($request->boolean('demo_card')) {
            $params['payment_method_types'] = ['card'];
            $params['payment_method'] = 'pm_card_visa';
            $params['confirm'] = true;
        } else {
            $params['automatic_payment_methods'] = ['enabled' => true];
        }

        $paymentIntent = PaymentIntent::create($params);

        return response()->json([
            'client_secret'     => $paymentIntent->client_secret,
            'payment_intent_id' => $paymentIntent->id,
            'status'            => $paymentIntent->status,
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
            'payment_method'    => 'required|string',
            'payment_intent_id' => 'nullable|string',
            'idempotency_key'   => 'nullable|string',
        ]);

        // The app auto-retries this request on a timeout, resending the same
        // idempotency_key. If a previous attempt already created the booking
        // (its response was just lost in transit), return that one instead
        // of creating a duplicate.
        $idempotencyKey = $request->input('idempotency_key');

        if ($idempotencyKey) {
            $existing = Booking::where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                $existing->load('service');
                return response()->json([
                    'message' => 'Booking confirmed!',
                    'booking' => $existing,
                ], 201);
            }
        }

        $service = Service::find($request->service_id);

        if (!$service) {
            return response()->json(['message' => 'Service not found'], 422);
        }

        $address = Address::where('id', $request->address_id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$address) {
            return response()->json([
                'message' => 'Address not found or does not belong to you.',
            ], 422);
        }

        // total_price is always the authoritative Service price — the
        // client's number is never trusted for what gets stored or charged.
        $totalPrice = (float) $service->price;
        $paymentStatus = 'pending';

        if ($request->payment_method === 'card') {
            if (!$request->payment_intent_id) {
                return response()->json([
                    'message' => 'Missing payment confirmation for a card payment.',
                ], 422);
            }

            Stripe::setApiKey(config('services.stripe.secret'));

            try {
                $intent = PaymentIntent::retrieve($request->payment_intent_id);
            } catch (\Exception) {
                return response()->json(['message' => 'Invalid payment reference.'], 422);
            }

            if ($intent->status !== 'succeeded') {
                return response()->json(['message' => 'Payment has not been completed.'], 402);
            }

            $expectedAmount = (int) round($totalPrice * 100);

            if ((int) $intent->amount !== $expectedAmount
                || (int) ($intent->metadata->service_id ?? 0) !== $service->id
                || (int) ($intent->metadata->user_id ?? 0) !== $request->user()->id
            ) {
                return response()->json(['message' => 'Payment does not match this booking.'], 422);
            }

            $paymentStatus = 'completed';
        }

        $lat = $lng = null;
        $geo = (new GeocodingService())->getCoordinates($address->address . ', ' . $address->city);
        $lat = $geo['latitude'] ?? null;
        $lng = $geo['longitude'] ?? null;

        try {
            $booking = Booking::create([
                'user_id'           => $request->user()->id,
                'service_id'        => $service->id,
                'worker_id'         => $request->worker_id,
                'address_id'        => $request->address_id,
                'scheduled_at'      => $request->scheduled_at,
                'total_price'       => $totalPrice,
                'payment_method'    => $request->payment_method,
                'payment_intent_id' => $request->payment_intent_id,
                'payment_status'    => $paymentStatus,
                'idempotency_key'   => $idempotencyKey,
                'latitude'          => $lat,
                'longitude'         => $lng,
                'status'            => 'pending',
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // A concurrent retry with the same idempotency_key (or
            // payment_intent_id) won the race and created the booking first —
            // return that one instead of surfacing the unique-constraint error.
            $existing = $idempotencyKey
                ? Booking::where('idempotency_key', $idempotencyKey)->first()
                : null;

            if (!$existing && $request->payment_intent_id) {
                $existing = Booking::where('payment_intent_id', $request->payment_intent_id)->first();
            }

            if (!$existing) {
                throw $e;
            }

            $existing->load('service');
            return response()->json([
                'message' => 'Booking confirmed!',
                'booking' => $existing,
            ], 201);
        }

        $booking->load('service');

        if ($paymentStatus === 'completed') {
            NotificationHelper::paymentSuccessful(
                userId: $request->user()->id,
                serviceName: $booking->service->title ?? 'Service',
                amount: $booking->total_price,
                bookingId: $booking->id,
            );
        }

        NotificationHelper::bookingConfirmed(
            userId: $request->user()->id,
            serviceName: $booking->service->title ?? 'Service',
            bookingId: $booking->id,
        );

        return response()->json([
            'message' => 'Booking confirmed!',
            'booking' => $booking,
        ], 201);
    }
}
