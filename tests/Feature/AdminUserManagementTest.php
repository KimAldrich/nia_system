<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    private const DEACTIVATED_MESSAGE = 'Your account is deactivated by the admin. Please contact the admin to reactivate your account.';

    public function test_admin_can_update_a_users_account_status(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'agreed_to_terms' => true,
        ]);

        $user = User::factory()->create([
            'role' => 'fs_team',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)
            ->withSession(['agreed_to_terms' => true])
            ->patch(route('admin.users.status', $user), [
                'is_active' => '0',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertFalse($user->fresh()->is_active);
    }

    public function test_admin_can_delete_a_user_account(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'agreed_to_terms' => true,
        ]);

        $user = User::factory()->create([
            'role' => 'fs_team',
        ]);

        $response = $this->actingAs($admin)
            ->withSession(['agreed_to_terms' => true])
            ->delete(route('admin.users.destroy', $user));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    public function test_deactivated_user_cannot_log_in(): void
    {
        $user = User::factory()->create([
            'role' => 'fs_team',
            'is_active' => false,
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'secret123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors([
            'email' => self::DEACTIVATED_MESSAGE,
        ]);
        $this->assertGuest();
    }

    public function test_logged_in_deactivated_user_is_logged_out_with_message(): void
    {
        $user = User::factory()->create([
            'role' => 'fs_team',
            'agreed_to_terms' => true,
            'is_active' => false,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['agreed_to_terms' => true])
            ->get(route('terms.show'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('deactivated_message', self::DEACTIVATED_MESSAGE);
        $this->assertGuest();
    }
}
