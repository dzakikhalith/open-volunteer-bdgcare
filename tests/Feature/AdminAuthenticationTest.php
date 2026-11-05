<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_accessible_for_guests(): void
    {
        $this->get(route('admin.login'))
            ->assertOk()
            ->assertSee('Login Dashboard Admin');
    }

    public function test_active_admin_can_login_to_the_dashboard(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@example.com',
            'password' => 'password-baru-123',
        ]);

        $response = $this->post(route('admin.login'), [
            'email' => $admin->email,
            'password' => 'password-baru-123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_login_fails_with_an_incorrect_password(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@example.com',
            'password' => 'password-baru-123',
        ]);

        $this->from(route('admin.login'))
            ->post(route('admin.login'), [
                'email' => $admin->email,
                'password' => 'password-yang-salah',
            ])
            ->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_inactive_admin_cannot_login(): void
    {
        $admin = User::factory()->admin()->inactive()->create([
            'email' => 'admin@example.com',
            'password' => 'password-baru-123',
        ]);

        $this->from(route('admin.login'))
            ->post(route('admin.login'), [
                'email' => $admin->email,
                'password' => 'password-baru-123',
            ])
            ->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_non_admin_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'email' => 'volunteer@example.com',
            'password' => 'password-baru-123',
        ]);

        $this->from(route('admin.login'))
            ->post(route('admin.login'), [
                'email' => $user->email,
                'password' => 'password-baru-123',
            ])
            ->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_admin_can_logout(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.logout'))
            ->assertRedirect(route('admin.login'));

        $this->assertGuest();
    }

    public function test_admin_password_command_resets_the_password_and_activates_the_account(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'role' => 'admin',
            'is_active' => false,
        ]);

        $this->artisan('admin:password', [
            '--email' => $admin->email,
            '--password' => 'password-baru-123',
        ])->assertSuccessful();

        $admin->refresh();

        $this->assertTrue($admin->is_active);
        $this->assertSame('admin', $admin->role);
        $this->assertTrue(Hash::check('password-baru-123', $admin->password));
    }

    public function test_admin_password_command_creates_an_admin_when_the_email_does_not_exist(): void
    {
        $this->artisan('admin:password', [
            '--email' => 'admin-baru@example.com',
            '--name' => 'Admin Baru',
            '--password' => 'password-baru-123',
        ])->assertSuccessful();

        $admin = User::query()->where('email', 'admin-baru@example.com')->firstOrFail();

        $this->assertSame('Admin Baru', $admin->name);
        $this->assertSame('admin', $admin->role);
        $this->assertTrue($admin->is_active);
        $this->assertTrue(Hash::check('password-baru-123', $admin->password));
    }
}
