<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_non_admin_user_cannot_access_dashboard(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_dashboard_displays_real_statistics(): void
    {
        $event = Event::factory()->create();
        Event::factory()->create();
        Event::factory()->closed()->create();

        Registration::factory()->for($event)->create(['status' => Registration::STATUS_PENDING]);
        Registration::factory()->for($event)->create(['status' => Registration::STATUS_PENDING]);
        Registration::factory()->for($event)->create(['status' => Registration::STATUS_ACCEPTED]);

        $response = $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSee('Dashboard')
            ->assertViewHas('stats', function (array $stats): bool {
                return $stats['total_events'] === 3
                    && $stats['open_events'] === 2
                    && $stats['total_registrations'] === 3
                    && $stats['pending_registrations'] === 2;
            });
    }

    public function test_dashboard_lists_the_five_most_recent_registrations(): void
    {
        $event = Event::factory()->create();

        foreach (range(1, 6) as $day) {
            Registration::factory()->for($event)->create([
                'full_name' => 'Pendaftar '.$day,
                'registered_at' => now()->subDays($day),
            ]);
        }

        $response = $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSee('Pendaftar 1')
            ->assertSee('Pendaftar 5')
            ->assertDontSee('Pendaftar 6')
            ->assertViewHas('recentRegistrations', fn ($registrations): bool => $registrations->count() === 5);
    }
}
