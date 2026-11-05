<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $this->get(route('admin.admins.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_non_admin_user_cannot_manage_admin_accounts(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.admins.index'))
            ->assertForbidden();
    }

    public function test_index_lists_only_admin_accounts_and_supports_search(): void
    {
        User::factory()->admin()->create(['name' => 'Admin Satu', 'email' => 'satu@example.com']);
        User::factory()->admin()->create(['name' => 'Admin Dua', 'email' => 'dua@example.com']);
        User::factory()->create(['name' => 'User Biasa', 'email' => 'user@example.com']);

        $response = $this->actingAs($this->admin())
            ->get(route('admin.admins.index', ['search' => 'Satu']));

        $response->assertOk()
            ->assertSee('Admin Satu')
            ->assertDontSee('Admin Dua')
            ->assertDontSee('User Biasa');
    }

    public function test_create_form_renders(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.admins.create'))
            ->assertOk()
            ->assertSee('Tambah Admin');
    }

    public function test_store_creates_active_admin_with_hashed_password(): void
    {
        $response = $this->actingAs($this->admin())->post(route('admin.admins.store'), [
            'name' => 'Admin Baru',
            'email' => 'baru@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'is_active' => '1',
        ]);

        $admin = User::where('email', 'baru@example.com')->sole();

        $response->assertRedirect(route('admin.admins.index'))
            ->assertSessionHas('success');

        $this->assertSame('admin', $admin->role);
        $this->assertTrue($admin->is_active);
        $this->assertTrue(Hash::check('rahasia123', $admin->password));
    }

    public function test_store_validates_password_and_unique_email(): void
    {
        User::factory()->create(['email' => 'dipakai@example.com']);

        $this->actingAs($this->admin())->post(route('admin.admins.store'), [
            'name' => 'Admin Baru',
            'email' => 'dipakai@example.com',
            'password' => 'pendek',
            'password_confirmation' => 'beda',
            'is_active' => '1',
        ])->assertSessionHasErrors(['email', 'password']);

        $this->assertDatabaseMissing('users', ['name' => 'Admin Baru']);
    }

    public function test_regular_user_cannot_be_managed_through_admin_account_routes(): void
    {
        $user = User::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('admin.admins.edit', $user))
            ->assertNotFound();
    }

    public function test_edit_form_renders_existing_admin(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Admin Lama']);

        $this->actingAs($this->admin())
            ->get(route('admin.admins.edit', $admin))
            ->assertOk()
            ->assertSee('Admin Lama')
            ->assertSee('Simpan Perubahan');
    }

    public function test_update_changes_account_without_replacing_password_when_left_empty(): void
    {
        $password = 'password-lama';
        $admin = User::factory()->admin()->create([
            'name' => 'Admin Lama',
            'email' => 'lama@example.com',
            'password' => Hash::make($password),
        ]);

        $response = $this->actingAs($this->admin())->put(route('admin.admins.update', $admin), [
            'name' => 'Admin Baru',
            'email' => 'baru@example.com',
            'password' => '',
            'password_confirmation' => '',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.admins.index'))
            ->assertSessionHas('success');

        $admin->refresh();

        $this->assertSame('Admin Baru', $admin->name);
        $this->assertSame('baru@example.com', $admin->email);
        $this->assertSame('admin', $admin->role);
        $this->assertTrue(Hash::check($password, $admin->password));
    }

    public function test_update_can_replace_password(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($this->admin())->put(route('admin.admins.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'password' => 'password-baru',
            'password_confirmation' => 'password-baru',
            'is_active' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('password-baru', $admin->fresh()->password));
    }

    public function test_update_can_deactivate_another_admin(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($this->admin())->put(route('admin.admins.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'is_active' => '0',
        ])->assertSessionHas('success');

        $this->assertFalse($admin->fresh()->is_active);
    }

    public function test_admin_cannot_deactivate_own_account(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->from(route('admin.admins.edit', $admin))
            ->put(route('admin.admins.update', $admin), [
                'name' => $admin->name,
                'email' => $admin->email,
                'is_active' => '0',
            ])
            ->assertRedirect(route('admin.admins.edit', $admin))
            ->assertSessionHas('error');

        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_admin_cannot_delete_own_account(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->from(route('admin.admins.edit', $admin))
            ->delete(route('admin.admins.destroy', $admin))
            ->assertRedirect(route('admin.admins.edit', $admin))
            ->assertSessionHas('error');

        $this->assertModelExists($admin);
    }

    public function test_admin_can_delete_another_admin(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($this->admin())
            ->delete(route('admin.admins.destroy', $admin))
            ->assertRedirect(route('admin.admins.index'))
            ->assertSessionHas('success');

        $this->assertModelMissing($admin);
    }
}
