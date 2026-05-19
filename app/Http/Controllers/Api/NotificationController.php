<?php
// app/Http/Controllers/Api/NotificationController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // Get all notifications for current user
    public function index(Request $request)
    {
        $notifications = Notification::where(
            'user_id',
            $request->user()->id
        )
            ->orderBy('created_at', 'desc')
            ->get();

        // Group by date
        $grouped = $notifications->groupBy(function ($notif) {
            $diff = now()->diffInDays($notif->created_at);
            if ($diff == 0) return 'Today';
            if ($diff == 1) return 'Yesterday';
            return 'Earlier';
        });

        return response()->json([
            'notifications' => $notifications,
            'grouped'       => $grouped,
            'unread_count'  => $notifications->where('is_read', false)->count(),
        ]);
    }

    // Mark single notification as read
    public function markAsRead(Request $request, $id)
    {
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
        Notification::where('user_id', $request->user()->id)
            ->delete();

        return response()->json([
            'message' => 'All notifications cleared',
        ]);
    }
}
