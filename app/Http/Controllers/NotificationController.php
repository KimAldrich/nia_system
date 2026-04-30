<?php

namespace App\Http\Controllers;

use App\Services\SystemNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(SystemNotificationService::MAX_NOTIFICATIONS)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->data['type'] ?? 'general',
                    'title' => $notification->data['title'] ?? 'Notification',
                    'category_label' => $notification->data['category_label'] ?? 'Update',
                    'category_color' => $notification->data['category_color'] ?? 'slate',
                    'message' => $notification->data['message'] ?? '',
                    'url' => $notification->data['url'] ?? null,
                    'created_at' => optional($notification->created_at)->toIso8601String(),
                    'read_at' => optional($notification->read_at)->toIso8601String(),
                ];
            })
            ->values();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
        ]);
    }
}
