<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicEventListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_published_events_are_listed(): void
    {
        Event::factory()->create(['title' => 'Event Publik']);
        Event::factory()->unpublished()->create(['title' => 'Event Draft']);

        $this->get(route('events.index'))
            ->assertOk()
            ->assertSee('Event Publik')
            ->assertDontSee('Event Draft');
    }

    public function test_events_can_be_searched_and_filtered_by_category_and_status(): void
    {
        $education = Category::factory()->create(['name' => 'Pendidikan', 'slug' => 'pendidikan']);
        $environment = Category::factory()->create(['name' => 'Lingkungan', 'slug' => 'lingkungan']);

        Event::factory()->for($education)->create([
            'title' => 'Mengajar Anak',
            'recruitment_status' => 'OPEN',
        ]);
        Event::factory()->for($environment)->closed()->create([
            'title' => 'Bersih Pantai',
        ]);

        $this->get(route('events.index', [
            'search' => 'Mengajar',
            'category' => 'pendidikan',
            'status' => 'OPEN',
        ]))
            ->assertOk()
            ->assertSee('Mengajar Anak')
            ->assertDontSee('Bersih Pantai');
    }

    public function test_events_can_be_sorted_by_date(): void
    {
        Event::factory()->create([
            'title' => 'Event Lama',
            'event_start_at' => now()->addDays(2),
        ]);
        Event::factory()->create([
            'title' => 'Event Baru',
            'event_start_at' => now()->addDays(10),
        ]);

        $response = $this->get(route('events.index', ['sort' => 'date_asc']));

        $response->assertOk();
        $response->assertSeeInOrder(['Event Lama', 'Event Baru']);
    }

    public function test_closed_events_are_still_visible_but_cannot_be_registered(): void
    {
        $event = Event::factory()->closed()->create(['title' => 'Event Ditutup']);

        $this->get(route('events.index'))
            ->assertOk()
            ->assertSee('Event Ditutup');

        $this->get(route('events.register', $event))
            ->assertRedirect(route('events.show', $event))
            ->assertSessionHas('error');
    }
}
