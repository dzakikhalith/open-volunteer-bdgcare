<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSettingManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $this->get(route('admin.settings.edit'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_non_admin_user_cannot_manage_settings(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.settings.edit'))
            ->assertForbidden();
    }

    public function test_admin_can_update_website_settings(): void
    {
        $response = $this->actingAs(User::factory()->admin()->create())
            ->put(route('admin.settings.update'), [
                'site_name' => 'Bandung Care Baru',
                'tagline' => 'Tagline Baru',
                'description' => 'Deskripsi baru.',
                'hero_title' => 'Judul Hero Baru',
                'hero_description' => 'Deskripsi hero baru.',
                'about_text' => 'Tentang kami yang baru.',
                'contact_email' => 'halo@bandungcare.id',
                'contact_phone' => '08123456789',
                'contact_address' => 'Jl. Merdeka No. 1',
                'instagram' => '@bandungcarebaru',
                'footer_text' => 'Footer baru.',
            ]);

        $response->assertRedirect(route('admin.settings.edit'))
            ->assertSessionHas('success');

        $this->assertSame('Bandung Care Baru', SiteSetting::get('site_name'));
        $this->assertSame('halo@bandungcare.id', SiteSetting::get('contact_email'));
    }

    public function test_admin_settings_are_shown_on_the_public_homepage(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->put(route('admin.settings.update'), [
                'site_name' => 'Bandung Care Baru',
                'tagline' => 'Tagline Baru',
                'description' => 'Deskripsi baru.',
                'hero_title' => 'Jadilah Relawan Hebat',
                'hero_description' => 'Deskripsi hero baru.',
                'about_text' => 'Tentang kami yang baru.',
                'contact_email' => 'halo@bandungcare.id',
                'contact_phone' => '08123456789',
                'contact_address' => 'Jl. Merdeka No. 1',
                'instagram' => '@bandungcarebaru',
                'footer_text' => 'Footer baru.',
            ])->assertSessionHasNoErrors();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Jadilah Relawan Hebat')
            ->assertSee('Tentang kami yang baru.')
            ->assertSee('halo@bandungcare.id')
            ->assertSee('@bandungcarebaru');
    }

    public function test_settings_update_validates_the_contact_email(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->put(route('admin.settings.update'), [
                'site_name' => 'Bandung Care',
                'contact_email' => 'bukan-email',
            ])->assertSessionHasErrors('contact_email');
    }
}
