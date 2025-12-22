<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * Get all notifications for current user
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $companyId = session('active_company_id');

            $query = Notification::with('actor')
                ->where('user_id', $user->id)
                ->where('company_id', $companyId);

            // Filter by type if provided
            if ($request->has('type') && $request->type !== 'all') {
                $query->where('type', $request->type);
            }

            // Get notifications
            $notifications = $query->orderBy('created_at', 'desc')
                ->limit(50)
                ->get()
                ->map(function ($notification) {
                    return [
                        'id' => $notification->id,
                        'type' => $notification->type,
                        'title' => $notification->title,
                        'message' => $notification->message,
                        'context' => $notification->context,
                        'user_name' => $notification->actor_info['name'],
                        'avatar' => $notification->actor_info['avatar'],
                        'is_read' => $notification->is_read,
                        'action_url' => $notification->action_url,
                        'time' => $notification->formatted_time,
                        'created_at' => $notification->created_at->toIso8601String(),
                    ];
                });

            return response()->json([
                'success' => true,
                'notifications' => $notifications
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching notifications: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil notifikasi'
            ], 500);
        }
    }

    /**
     * Get unread count
     */
    public function getUnreadCount()
    {
        try {
            $user = Auth::user();
            $companyId = session('active_company_id');

            $count = Notification::where('user_id', $user->id)
                ->where('company_id', $companyId)
                ->where('is_read', false)
                ->count();

            return response()->json([
                'success' => true,
                'count' => $count
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting unread count: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil jumlah notifikasi'
            ], 500);
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        try {
            $user = Auth::user();
            
            $notification = Notification::where('id', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            $notification->markAsRead();

            return response()->json([
                'success' => true,
                'message' => 'Notifikasi ditandai sudah dibaca'
            ]);
        } catch (\Exception $e) {
            Log::error('Error marking notification as read: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menandai notifikasi'
            ], 500);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        try {
            $user = Auth::user();
            $companyId = session('active_company_id');

            Notification::where('user_id', $user->id)
                ->where('company_id', $companyId)
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Semua notifikasi ditandai sudah dibaca'
            ]);
        } catch (\Exception $e) {
            Log::error('Error marking all as read: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menandai semua notifikasi'
            ], 500);
        }
    }

    /**
     * Delete notification
     */
    public function destroy($id)
    {
        try {
            $user = Auth::user();
            
            $notification = Notification::where('id', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            $notification->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notifikasi berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting notification: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus notifikasi'
            ], 500);
        }
    }

    /**
     * Clear all read notifications
     */
    public function clearRead()
    {
        try {
            $user = Auth::user();
            $companyId = session('active_company_id');

            Notification::where('user_id', $user->id)
                ->where('company_id', $companyId)
                ->where('is_read', true)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notifikasi yang sudah dibaca berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            Log::error('Error clearing read notifications: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus notifikasi'
            ], 500);
        }
    }
}