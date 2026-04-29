<?php

namespace Tests\Feature;

use App\Models\IaResolution;
use App\Models\User;
use App\Notifications\SystemActivityNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class IaResolutionStatusNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_updating_fs_resolution_status_notifies_fs_team_and_other_admins_only(): void
    {
        Notification::fake();

        $admin = User::factory()->create([
            'role' => 'admin',
            'agreed_to_terms' => true,
            'is_active' => true,
        ]);

        $otherAdmin = User::factory()->create([
            'role' => 'admin',
            'agreed_to_terms' => true,
            'is_active' => true,
        ]);

        $fsMember = User::factory()->create([
            'role' => 'fs_team',
            'agreed_to_terms' => true,
            'is_active' => true,
        ]);

        $rowMember = User::factory()->create([
            'role' => 'row_team',
            'agreed_to_terms' => true,
            'is_active' => true,
        ]);

        $resolution = IaResolution::create([
            'title' => 'FS Resolution 1',
            'file_path' => 'resolutions/fs-resolution-1.pdf',
            'original_name' => 'fs-resolution-1.pdf',
            'status' => 'not-validated',
            'team' => 'fs_team',
        ]);

        $response = $this->actingAs($admin)
            ->withSession(['agreed_to_terms' => true])
            ->post(route('fs.resolutions.update_status', $resolution->id), [
                'status' => 'validated',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Resolution status updated successfully.');
        $this->assertSame('validated', $resolution->fresh()->status);

        foreach ([$otherAdmin, $fsMember] as $recipient) {
            Notification::assertSentTo(
                $recipient,
                SystemActivityNotification::class,
                function (SystemActivityNotification $notification, array $channels, object $notifiable) {
                    $payload = $notification->toArray($notifiable);

                    return $channels === ['database']
                        && $payload['type'] === 'ia_resolution_status'
                        && $payload['team'] === 'fs_team'
                        && $payload['title'] === 'IA resolution status changed'
                        && str_contains($payload['message'], 'changed the status of FS Resolution 1 in FS Team from not-validated to validated.');
                }
            );
        }

        Notification::assertNotSentTo($admin, SystemActivityNotification::class);
        Notification::assertNotSentTo($rowMember, SystemActivityNotification::class);
    }
}
