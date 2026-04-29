<?php

namespace Tests\Feature;

use App\Models\IaResolution;
use App\Models\User;
use App\Notifications\SystemActivityNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class IaResolutionNotificationScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_updating_fs_resolution_file_notifies_fs_team_and_other_admins_only(): void
    {
        Storage::fake('public');
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

        Storage::disk('public')->put('resolutions/original.pdf', 'old');

        $resolution = IaResolution::create([
            'title' => 'FS Resolution 2',
            'file_path' => 'resolutions/original.pdf',
            'original_name' => 'original.pdf',
            'status' => 'not-validated',
            'team' => 'fs_team',
        ]);

        $response = $this->actingAs($admin)
            ->withSession(['agreed_to_terms' => true])
            ->post(route('fs.resolutions.update', $resolution->id), [
                'document' => UploadedFile::fake()->create('replacement.pdf', 10, 'application/pdf'),
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Resolution updated successfully.');

        foreach ([$otherAdmin, $fsMember] as $recipient) {
            Notification::assertSentTo(
                $recipient,
                SystemActivityNotification::class,
                function (SystemActivityNotification $notification, array $channels, object $notifiable) {
                    $payload = $notification->toArray($notifiable);

                    return $channels === ['database']
                        && $payload['type'] === 'ia_resolution'
                        && $payload['team'] === 'fs_team'
                        && $payload['title'] === 'IA resolution updated'
                        && str_contains($payload['message'], 'replaced original.pdf with replacement.pdf in FS Team IA resolutions.');
                }
            );
        }

        Notification::assertNotSentTo($admin, SystemActivityNotification::class);
        Notification::assertNotSentTo($rowMember, SystemActivityNotification::class);
    }
}
