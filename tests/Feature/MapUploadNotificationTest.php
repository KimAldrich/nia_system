<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\SystemActivityNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MapUploadNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_map_upload_notifies_other_active_users_with_file_and_directory(): void
    {
        Storage::fake('public');
        Notification::fake();

        $admin = User::factory()->create([
            'role' => 'admin',
            'agreed_to_terms' => true,
        ]);

        $recipientOne = User::factory()->create([
            'role' => 'fs_team',
            'agreed_to_terms' => true,
            'is_active' => true,
        ]);

        $recipientTwo = User::factory()->create([
            'role' => 'row_team',
            'agreed_to_terms' => true,
            'is_active' => true,
        ]);

        $inactiveUser = User::factory()->create([
            'role' => 'pcr_team',
            'agreed_to_terms' => true,
            'is_active' => false,
        ]);

        $response = $this->actingAs($admin)
            ->withSession(['agreed_to_terms' => true])
            ->post(route('map.upload'), [
                'category' => 'Irrigated Area',
                'target_folder' => 'Agno',
                'files' => [
                    UploadedFile::fake()->create('sample-map.geojson', 10, 'application/geo+json'),
                ],
            ]);

        $response->assertOk();
        $response->assertJsonFragment([
            'target_folder' => 'Agno',
        ]);

        foreach ([$recipientOne, $recipientTwo] as $recipient) {
            Notification::assertSentTo(
                $recipient,
                SystemActivityNotification::class,
                function (SystemActivityNotification $notification, array $channels, object $notifiable) {
                    $payload = $notification->toArray($notifiable);

                    return $channels === ['database']
                        && $payload['type'] === 'map_file'
                        && $payload['title'] === 'New map file uploaded'
                        && str_contains($payload['message'], $payload['actor_name'] . ' (Admin) uploaded')
                        && str_contains($payload['message'], 'sample-map.geojson')
                        && str_contains($payload['message'], 'maps/irrigated/Agno')
                        && str_contains($payload['message'], 'Irrigated Area')
                        && $payload['map_directory'] === 'maps/irrigated/Agno'
                        && $payload['map_files'] === ['sample-map.geojson']
                        && $payload['url'] === route('map');
                }
            );
        }

        Notification::assertNotSentTo($admin, SystemActivityNotification::class);
        Notification::assertNotSentTo($inactiveUser, SystemActivityNotification::class);
    }
}
