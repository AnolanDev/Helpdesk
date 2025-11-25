<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get notifications for authenticated user
     */
    public function index(Request $request)
    {
        $notifications = Notification::forUser(auth()->id())
            ->with(['ticket', 'actionBy'])
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => Notification::forUser(auth()->id())->unread()->count(),
        ]);
    }

    /**
     * Get unread count
     */
    public function unreadCount()
    {
        return response()->json([
            'count' => Notification::forUser(auth()->id())->unread()->count(),
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Notification $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        Notification::forUser(auth()->id())
            ->unread()
            ->update([
                'read' => true,
                'read_at' => now(),
            ]);

        return response()->json(['success' => true]);
    }

    /**
     * Delete notification
     */
    public function destroy(Notification $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Delete all read notifications
     */
    public function deleteAllRead()
    {
        Notification::forUser(auth()->id())
            ->read()
            ->delete();

        return response()->json(['success' => true]);
    }
}
