<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SystemNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationRetentionTest extends TestCase
{
    use RefreshDatabase;

    public function test_notifications_keep_only_latest_hundred_and_feed_returns_hundred(): void
    {
        $actor = User::factory()->create([
            'role' => 'fs_team',
            'agreed_to_terms' => true,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $recipient = User::factory()->create([
            'role' => 'admin',
            'agreed_to_terms' => true,
            'is_active' => true,
        ]);

        $service = app(SystemNotificationService::class);

        foreach (range(1, 101) as $number) {
            $service->notifyTeamAndAdmins(
                $actor,
                'fs_team',
                "Notification {$number}",
                "Message {$number}",
                [
                    'type' => 'downloadable',
                    'team' => 'fs_team',
                ]
            );
        }

        $storedNotifications = $recipient->notifications()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $this->assertCount(SystemNotificationService::MAX_NOTIFICATIONS, $storedNotifications);
        $this->assertSame('Notification 101', $storedNotifications->first()->data['title']);
        $this->assertSame('Notification 2', $storedNotifications->last()->data['title']);
        $this->assertFalse($storedNotifications->contains(fn ($notification) => $notification->data['title'] === 'Notification 1'));

        $response = $this->actingAs($recipient)
            ->withSession(['agreed_to_terms' => true])
            ->getJson(route('notifications.index'));

        $response->assertOk();
        $response->assertJsonCount(SystemNotificationService::MAX_NOTIFICATIONS, 'notifications');
        $response->assertJsonPath('notifications.0.title', 'Notification 101');
        $response->assertJsonPath('notifications.99.title', 'Notification 2');
    }
}
