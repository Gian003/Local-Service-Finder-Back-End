<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Worker;
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
    public function show(Request $request, $id)
    {
        $booking = Booking::with([
            'service',
            'worker',
            'user',
            'address',
        ])->find($id);

        if (!$booking || !$this->isParticipant($request, $booking)) {
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

        if (!$this->isAssignedWorker($request, $booking)) {
            return response()->json([
                'message' => 'This booking does not belong to you.'
            ], 403);
        }

        if ($booking->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending bookings can be accepted.'
            ], 422);
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

        if (!$this->isAssignedWorker($request, $booking)) {
            return response()->json([
                'message' => 'This booking does not belong to you.'
            ], 403);
        }

        if ($booking->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending bookings can be rejected.'
            ], 422);
        }

        $booking->update(['status' => 'rejected']);
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

        if (!$this->isAssignedWorker($request, $booking)) {
            return response()->json([
                'message' => 'This booking does not belong to you.'
            ], 403);
        }

        if ($booking->status !== 'accepted') {
            return response()->json([
                'message' => 'Only accepted bookings can be marked completed.'
            ], 422);
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

        if (!$this->isBookingOwner($request, $booking)) {
            return response()->json([
                'message' => 'This booking does not belong to you.'
            ], 403);
        }

        if (in_array($booking->status, ['completed', 'cancelled'], true)) {
            return response()->json([
                'message' => 'This booking can no longer be cancelled.'
            ], 422);
        }

        $booking->update(['status' => 'cancelled']);
        $booking->load(['service', 'worker', 'user', 'address']);

        return response()->json([
            'message' => 'Booking cancelled',
            'booking' => $booking,
        ]);
    }

    // The customer who placed the booking — checked against type as well as id,
    // since users.id and workers.id are independent sequences and could collide.
    private function isBookingOwner(Request $request, Booking $booking): bool
    {
        $actor = $request->user();
        return !($actor instanceof Worker) && $booking->user_id === $actor->id;
    }

    // The worker assigned to the booking — see isBookingOwner() for why the
    // type check matters alongside the id check.
    private function isAssignedWorker(Request $request, Booking $booking): bool
    {
        $actor = $request->user();
        return $actor instanceof Worker && $booking->worker_id === $actor->id;
    }

    private function isParticipant(Request $request, Booking $booking): bool
    {
        return $this->isBookingOwner($request, $booking)
            || $this->isAssignedWorker($request, $booking);
    }
}
