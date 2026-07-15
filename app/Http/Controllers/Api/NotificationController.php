<?php
// app/Http/Controllers/Api/NotificationController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Worker;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // Notifications are only ever created for customers today (see every
    // NotificationHelper call site) — notifications.user_id is a plain FK to
    // `users`, and since Worker ids are an independent sequence, a Worker
    // whose id happens to match a real customer's id must not be able to
    // read or mutate that customer's notifications.
    private function isCustomer(Request $request): bool
    {
        return !($request->user() instanceof Worker);
    }

    // Get all notifications for current user
    public function index(Request $request)
    {
        if (!$this->isCustomer($request)) {
            return response()->json(['message' => 'Not available for worker accounts.'], 403);
        }

        $notifications = Notification::where(
            'user_id',
            $request->user()->id
        )
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $notifications,
            'unread_count'  => $notifications->where('is_read', false)->count(),
        ]);
    }

    // Mark single notification as read
    public function markAsRead(Request $request, $id)
    {
        if (!$this->isCustomer($request)) {
            return response()->json(['message' => 'Not available for worker accounts.'], 403);
        }

        $notification = Notification::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$notification) {
            return response()->json([
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->update(['is_read' => true]);

        return response()->json([
            'message'      => 'Marked as read',
            'notification' => $notification,
        ]);
    }

    // Mark all as read
    public function markAllAsRead(Request $request)
    {
        if (!$this->isCustomer($request)) {
            return response()->json(['message' => 'Not available for worker accounts.'], 403);
        }

        Notification::where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'message' => 'All notifications marked as read',
        ]);
    }

    // Delete single notification
    public function destroy(Request $request, $id)
    {
        if (!$this->isCustomer($request)) {
            return response()->json(['message' => 'Not available for worker accounts.'], 403);
        }

        Notification::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->delete();

        return response()->json([
            'message' => 'Notification deleted',
        ]);
    }

    // Delete all notifications
    public function destroyAll(Request $request)
    {
        if (!$this->isCustomer($request)) {
            return response()->json(['message' => 'Not available for worker accounts.'], 403);
        }

        Notification::where('user_id', $request->user()->id)
            ->delete();

        return response()->json([
            'message' => 'All notifications cleared',
        ]);
    }
}
