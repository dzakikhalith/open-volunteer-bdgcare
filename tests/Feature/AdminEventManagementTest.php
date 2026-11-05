<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminEventManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    private function fakePng(string $name = 'poster.png'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            $name,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==')
        );
    }

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $this->get(route('admin.events.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_non_admin_user_cannot_access_event_management(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.events.index'))
            ->assertForbidden();
    }

    public function test_inactive_admin_cannot_access_event_management(): void
    {
        $this->actingAs(User::factory()->admin()->inactive()->create())
            ->get(route('admin.events.index'))
            ->assertForbidden();
    }

    public function test_index_lists_events_and_supports_search_and_filters(): void
    {
        $category = Category::factory()->create(['name' => 'Lingkungan']);
        $otherCategory = Category::factory()->create(['name' => 'Pendidikan']);

        Event::factory()->for($category)->create(['title' => 'Bersih Pantai Bandung']);
        Event::factory()->for($otherCategory)->closed()->create(['title' => 'Les Anak Yatim']);

        $response = $this->actingAs($this->admin())
            ->get(route('admin.events.index', ['search' => 'Pantai', 'category' => $category->slug, 'status' => 'OPEN']));

        $response->assertOk()
            ->assertSee('Bersih Pantai Bandung')
            ->assertDontSee('Les Anak Yatim');
    }

    public function test_create_form_renders(): void
    {
        Category::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('admin.events.create'))
            ->assertOk()
            ->assertSee('Tambah Event');
    }

    public function test_store_creates_event_with_generated_slug_and_creator(): void
    {
        $admin = $this->admin();
        $category = Category::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.events.store'), [
            'category_id' => $category->id,
            'title' => 'Kerja Bakti Cikapundung',
            'summary' => 'Membersihkan sungai bersama warga.',
            'description' => 'Kegiatan kerja bakti bulanan.',
            'location' => 'Sungai Cikapundung',
            'event_start_at' => '2026-11-10 08:00:00',
            'event_end_at' => '2026-11-10 12:00:00',
            'registration_start_at' => '2026-11-01 08:00:00',
            'registration_end_at' => '2026-11-08 20:00:00',
            'quota' => 30,
            'requirements' => 'Membawa pakaian ganti.',
            'recruitment_status' => 'OPEN',
            'is_published' => '1',
        ]);

        $event = Event::sole();

        $response->assertRedirect(route('admin.events.edit', $event))
            ->assertSessionHas('success');

        $this->assertSame('kerja-bakti-cikapundung', $event->slug);
        $this->assertSame($admin->id, $event->created_by);
        $this->assertTrue($event->is_published);
        $this->assertSame(30, $event->quota);
    }

    public function test_store_uploads_poster_to_public_disk(): void
    {
        Storage::fake('public');
        $category = Category::factory()->create();

        $this->actingAs($this->admin())->post(route('admin.events.store'), [
            'category_id' => $category->id,
            'title' => 'Donor Darah Bandung',
            'location' => 'Balai Kota Bandung',
            'event_start_at' => '2026-11-10 08:00:00',
            'recruitment_status' => 'OPEN',
            'is_published' => '1',
            'poster' => $this->fakePng(),
        ])->assertSessionHasNoErrors();

        $event = Event::sole();

        $this->assertNotNull($event->poster_path);
        Storage::disk('public')->assertExists($event->poster_path);
    }

    public function test_update_replaces_existing_poster_and_deletes_the_old_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('event-posters/old.png', 'old poster');

        $event = Event::factory()->create(['poster_path' => 'event-posters/old.png']);

        $this->actingAs($this->admin())->put(route('admin.events.update', $event), [
            'category_id' => $event->category_id,
            'title' => $event->title,
            'slug' => $event->slug,
            'location' => $event->location,
            'event_start_at' => $event->event_start_at->format('Y-m-d H:i:s'),
            'recruitment_status' => $event->recruitment_status,
            'poster' => $this->fakePng('new.png'),
        ])->assertSessionHasNoErrors();

        $event->refresh();

        $this->assertNotSame('event-posters/old.png', $event->poster_path);
        Storage::disk('public')->assertMissing('event-posters/old.png');
        Storage::disk('public')->assertExists($event->poster_path);
    }

    public function test_update_can_remove_the_existing_poster(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('event-posters/old.png', 'old poster');

        $event = Event::factory()->create(['poster_path' => 'event-posters/old.png']);

        $this->actingAs($this->admin())->put(route('admin.events.update', $event), [
            'category_id' => $event->category_id,
            'title' => $event->title,
            'slug' => $event->slug,
            'location' => $event->location,
            'event_start_at' => $event->event_start_at->format('Y-m-d H:i:s'),
            'recruitment_status' => $event->recruitment_status,
            'remove_poster' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertNull($event->fresh()->poster_path);
        Storage::disk('public')->assertMissing('event-posters/old.png');
    }

    public function test_store_validates_required_fields(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.events.store'), [
                'title' => '',
                'location' => '',
                'event_start_at' => '',
                'recruitment_status' => 'INVALID',
            ])
            ->assertSessionHasErrors(['category_id', 'title', 'location', 'event_start_at', 'recruitment_status']);

        $this->assertDatabaseCount('events', 0);
    }

    public function test_store_generates_unique_slug_when_title_already_exists(): void
    {
        $category = Category::factory()->create();
        Event::factory()->for($category)->create(['slug' => 'kerja-bakti']);

        $this->actingAs($this->admin())->post(route('admin.events.store'), [
            'category_id' => $category->id,
            'title' => 'Kerja Bakti',
            'location' => 'Bandung',
            'event_start_at' => '2026-11-10 08:00:00',
            'recruitment_status' => 'DRAFT',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('events', ['slug' => 'kerja-bakti-2']);
        $this->assertDatabaseCount('events', 2);
    }

    public function test_edit_form_renders_existing_event(): void
    {
        $event = Event::factory()->create(['title' => 'Event Lama']);

        $this->actingAs($this->admin())
            ->get(route('admin.events.edit', $event))
            ->assertOk()
            ->assertSee('Event Lama')
            ->assertSee('Simpan Perubahan');
    }

    public function test_update_changes_event_attributes(): void
    {
        $category = Category::factory()->create();
        $event = Event::factory()->create(['title' => 'Judul Lama']);

        $response = $this->actingAs($this->admin())->put(route('admin.events.update', $event), [
            'category_id' => $category->id,
            'title' => 'Judul Baru',
            'slug' => 'judul-baru',
            'location' => 'Bandung',
            'event_start_at' => '2026-12-01 09:00:00',
            'recruitment_status' => 'CLOSED',
            'quota' => 15,
        ]);

        $response->assertRedirect(route('admin.events.edit', $event->fresh()))
            ->assertSessionHas('success');

        $event->refresh();

        $this->assertSame('Judul Baru', $event->title);
        $this->assertSame('judul-baru', $event->slug);
        $this->assertSame('CLOSED', $event->recruitment_status);
        $this->assertSame($category->id, $event->category_id);
        $this->assertFalse($event->is_published);
    }

    public function test_update_requires_a_slug_that_is_unique_across_events(): void
    {
        Event::factory()->create(['slug' => 'event-lain']);
        $event = Event::factory()->create();

        $this->actingAs($this->admin())->put(route('admin.events.update', $event), [
            'category_id' => $event->category_id,
            'title' => $event->title,
            'slug' => 'event-lain',
            'location' => $event->location,
            'event_start_at' => $event->event_start_at->format('Y-m-d H:i:s'),
            'recruitment_status' => $event->recruitment_status,
        ])->assertSessionHasErrors('slug');
    }

    public function test_destroy_soft_deletes_event(): void
    {
        $event = Event::factory()->create();

        $this->actingAs($this->admin())
            ->delete(route('admin.events.destroy', $event))
            ->assertRedirect(route('admin.events.index'))
            ->assertSessionHas('success');

        $this->assertSoftDeleted($event);
    }
}
