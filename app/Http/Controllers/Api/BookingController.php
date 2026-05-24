<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    // Get all bookings for current user
    public function userBookings(Request $request)
    {
        $bookings = Booking::where('user_id', $request->user()->id)
            ->with(['service', 'worker', 'address'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($bookings);
    }

    // Get all bookings for current worker
    public function workerBookings(Request $request)
    {
        $bookings = Booking::where('worker_id', $request->user()->id)
            ->with(['service', 'user', 'address'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($bookings);
    }

    // Get single booking
    public function show($id)
    {
        $booking = Booking::with([
            'service',
            'worker',
            'user',
            'address', // 👈 includes address
        ])->find($id);

        if (!$booking) {
            return response()->json([
                'message' => 'Booking not found'
            ], 404);
        }

        return response()->json($booking);
    }

    // Worker accepts booking
    public function accept(Request $request, $id)
    {
        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json([
                'message' => 'Booking not found'
            ], 404);
        }

        $booking->update(['status' => 'accepted']);
        $booking->load(['service', 'worker', 'user', 'address']);

        return response()->json([
            'message' => 'Booking accepted',
            'booking' => $booking,
        ]);
    }

    // Worker rejects booking
    public function reject(Request $request, $id)
    {
        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json([
                'message' => 'Booking not found'
            ], 404);
        }

        $booking->update(['status' => 'cancelled']);
        $booking->load(['service', 'worker', 'user', 'address']);

        return response()->json([
            'message' => 'Booking rejected',
            'booking' => $booking,
        ]);
    }

    // Worker marks booking as completed
    public function complete(Request $request, $id)
    {
        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json([
                'message' => 'Booking not found'
            ], 404);
        }

        $booking->update(['status' => 'completed']);
        $booking->load(['service', 'worker', 'user', 'address']);

        return response()->json([
            'message' => 'Booking completed',
            'booking' => $booking,
        ]);
    }

    // User cancels booking
    public function cancel(Request $request, $id)
    {
        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json([
                'message' => 'Booking not found'
            ], 404);
        }

        $booking->update(['status' => 'cancelled']);
        $booking->load(['service', 'worker', 'user', 'address']);

        return response()->json([
            'message' => 'Booking cancelled',
            'booking' => $booking,
        ]);
    }
}
