<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Carbon\Carbon;

class NotificationController extends Controller
{
    /**
     * Get all notifications
     */
    public function index()
    {
        $notifications = Notification::whereNotIn('notification_status', ['archived'])
            ->orderBy('notification_created', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => Notification::unread()->count()
        ]);
    }

    /**
     * Get unread notifications count
     */
    public function getUnreadCount()
    {
        $count = Notification::unread()->count();
        
        return response()->json([
            'success' => true,
            'unread_count' => $count
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        $notification = Notification::find($id);
        
        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->notification_status = 'read';
        $notification->save();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        Notification::unread()->update([
            'notification_status' => 'read'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read'
        ]);
    }

    /**
     * Clear all notifications (archive them)
     */
    public function clearAll()
    {
        Notification::whereNotIn('notification_status', ['archived'])->update([
            'notification_status' => 'archived'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications cleared'
        ]);
    }

    /**
     * Delete notification
     */
    public function destroy($id)
    {
        $notification = Notification::find($id);
        
        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->notification_status = 'archived';
        $notification->save();

        return response()->json([
            'success' => true,
            'message' => 'Notification archived'
        ]);
    }

    /**
     * Create a new notification
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cargo_receipt_id' => 'nullable|exists:cargo_receipt,cargo_receipt_id',
            'payment_id' => 'nullable|exists:payment,payment_id',
            'notification_message' => 'required|string|max:255',
            'notification_type' => 'required|in:cargo booking approval,payment received',
        ]);

        $notification = Notification::create([
            'cargo_receipt_id' => $validated['cargo_receipt_id'] ?? null,
            'payment_id' => $validated['payment_id'] ?? null,
            'notification_message' => $validated['notification_message'],
            'notification_type' => $validated['notification_type'],
            'notification_status' => 'approved',
            'notification_created' => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification created successfully',
            'notification' => $notification
        ], 201);
    }
}
