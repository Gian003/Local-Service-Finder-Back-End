<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Review;
use App\Models\Worker;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    // Get reviews for a worker
    public function workerReviews($workerId)
    {
        $reviews = Review::where('worker_id', $workerId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($reviews);
    }

    // Submit a review
    public function store(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|integer',
            'worker_id'  => 'required|integer',
            'rating'     => 'required|numeric|min:1|max:5',
            'comment'    => 'nullable|string|max:500',
        ]);

        $booking = Booking::find($request->booking_id);

        // Must be a real booking, belonging to this exact caller (not a
        // Worker whose id happens to collide with the real owner's — see
        // isBookingOwner() in BookingController for why the type check matters).
        if (!$booking
            || $request->user() instanceof Worker
            || $booking->user_id !== $request->user()->id
        ) {
            return response()->json([
                'message' => 'Booking not found.'
            ], 404);
        }

        if ((int) $booking->worker_id !== (int) $request->worker_id) {
            return response()->json([
                'message' => 'worker_id does not match this booking.'
            ], 422);
        }

        if ($booking->status !== 'completed') {
            return response()->json([
                'message' => 'Only completed bookings can be reviewed.'
            ], 422);
        }

        // Check if already reviewed
        $existing = Review::where('booking_id', $request->booking_id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'You have already reviewed this booking'
            ], 409);
        }

        $review = Review::create([
            'booking_id' => $request->input('booking_id'),
            'user_id'    => $request->user()->id,
            'worker_id'  => $request->input('worker_id'),
            'rating'     => $request->input('rating'),
            'comment'    => $request->input('comment'),
        ]);

        // Update worker's average rating
        $worker     = Worker::find($request->input('worker_id'));
        $avgRating  = Review::where('worker_id', $request->input('worker_id'))
            ->avg('rating');
        $reviewCount = Review::where('worker_id', $request->input('worker_id'))
            ->count();

        $worker->update([
            'rating'       => round($avgRating, 1),
            'review_count' => $reviewCount,
        ]);

        return response()->json($review, 201);
    }
}
