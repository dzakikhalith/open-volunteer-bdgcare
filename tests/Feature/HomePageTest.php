<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_excludes_events_whose_quota_is_full(): void
    {
        $event = Event::factory()->create(['title' => 'Event Kuota Penuh', 'quota' => 1]);
        Registration::factory()->for($event)->create();

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Event Kuota Penuh');
    }

    public function test_home_page_lists_only_events_open_for_registration(): void
    {
        Event::factory()->create(['title' => 'Bakti Sosial Bandung']);
        Event::factory()->closed()->create(['title' => 'Event Sudah Ditutup']);
        Event::factory()->unpublished()->create(['title' => 'Event Belum Tayang']);

        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('Bakti Sosial Bandung')
            ->assertDontSee('Event Sudah Ditutup')
            ->assertDontSee('Event Belum Tayang');
    }
}
