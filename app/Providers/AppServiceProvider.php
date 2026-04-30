<?php

namespace App\Providers;

use App\Services\SystemNotificationService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.app', function ($view) {
            $summary = [
                'enabled' => false,
                'notifications' => [],
                'unread_count' => 0,
            ];

            if (auth()->check() && !session('is_guest')) {
                $user = auth()->user();
                $summary = [
                    'enabled' => true,
                    'notifications' => $user->notifications()
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
                        ->values(),
                    'unread_count' => $user->unreadNotifications()->count(),
                ];
            }

            $view->with('appNotificationSummary', $summary);
        });
    }
}
