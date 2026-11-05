<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminEventQuestionManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $event = Event::factory()->create();

        $this->get(route('admin.events.questions.index', $event))
            ->assertRedirect(route('admin.login'));
    }

    public function test_non_admin_user_cannot_manage_questions(): void
    {
        $event = Event::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('admin.events.questions.index', $event))
            ->assertForbidden();
    }

    public function test_index_lists_questions_of_the_event(): void
    {
        $event = Event::factory()->create();
        EventQuestion::factory()->for($event)->create(['label' => 'Motivasi Bergabung']);
        EventQuestion::factory()->for(Event::factory())->create(['label' => 'Pertanyaan Event Lain']);

        $this->actingAs($this->admin())
            ->get(route('admin.events.questions.index', $event))
            ->assertOk()
            ->assertSee('Motivasi Bergabung')
            ->assertDontSee('Pertanyaan Event Lain');
    }

    public function test_create_form_renders(): void
    {
        $event = Event::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('admin.events.questions.create', $event))
            ->assertOk()
            ->assertSee('Tambah Pertanyaan');
    }

    public function test_store_creates_question_with_normalized_options_and_generated_key(): void
    {
        $event = Event::factory()->create();

        $response = $this->actingAs($this->admin())->post(route('admin.events.questions.store', $event), [
            'label' => 'Ukuran Kaos',
            'type' => EventQuestion::TYPE_SELECT,
            'options_text' => "S\nM\n L \n\nS",
            'is_required' => '1',
            'is_active' => '1',
            'sort_order' => 2,
        ]);

        $question = EventQuestion::sole();

        $response->assertRedirect(route('admin.events.questions.index', $event))
            ->assertSessionHas('success');

        $this->assertSame($event->id, $question->event_id);
        $this->assertSame('ukuran_kaos', $question->field_key);
        $this->assertSame(['S', 'M', 'L'], $question->options);
        $this->assertTrue($question->is_required);
        $this->assertTrue($question->is_active);
        $this->assertSame(2, $question->sort_order);
    }

    public function test_store_requires_options_for_choice_questions(): void
    {
        $event = Event::factory()->create();

        $this->actingAs($this->admin())->post(route('admin.events.questions.store', $event), [
            'label' => 'Minat',
            'type' => EventQuestion::TYPE_CHECKBOX,
            'is_active' => '1',
        ])->assertSessionHasErrors('options');

        $this->assertDatabaseCount('event_questions', 0);
    }

    public function test_store_rejects_duplicate_field_key_within_the_same_event(): void
    {
        $event = Event::factory()->create();
        EventQuestion::factory()->for($event)->create(['field_key' => 'motivation']);

        $this->actingAs($this->admin())->post(route('admin.events.questions.store', $event), [
            'label' => 'Motivasi Lagi',
            'field_key' => 'motivation',
            'type' => EventQuestion::TYPE_TEXT,
            'is_active' => '1',
        ])->assertSessionHasErrors('field_key');
    }

    public function test_same_field_key_is_allowed_on_different_events(): void
    {
        $event = Event::factory()->create();
        EventQuestion::factory()->for(Event::factory())->create(['field_key' => 'motivation']);

        $this->actingAs($this->admin())->post(route('admin.events.questions.store', $event), [
            'label' => 'Motivasi',
            'field_key' => 'motivation',
            'type' => EventQuestion::TYPE_TEXTAREA,
            'is_active' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertSame($event->id, EventQuestion::where('event_id', $event->id)->sole()->event_id);
    }

    public function test_edit_form_renders_existing_question(): void
    {
        $event = Event::factory()->create();
        $question = EventQuestion::factory()->for($event)->create([
            'label' => 'Alasan Bergabung',
            'options' => ['Karena ingin membantu'],
            'type' => EventQuestion::TYPE_SELECT,
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.events.questions.edit', [$event, $question]))
            ->assertOk()
            ->assertSee('Alasan Bergabung')
            ->assertSee('Karena ingin membantu');
    }

    public function test_question_from_another_event_is_not_accessible(): void
    {
        $event = Event::factory()->create();
        $question = EventQuestion::factory()->for(Event::factory())->create();

        $this->actingAs($this->admin())
            ->get(route('admin.events.questions.edit', [$event, $question]))
            ->assertNotFound();
    }

    public function test_update_changes_question_attributes(): void
    {
        $event = Event::factory()->create();
        $question = EventQuestion::factory()->for($event)->create([
            'label' => 'Lama',
            'field_key' => 'lama',
        ]);

        $response = $this->actingAs($this->admin())->put(route('admin.events.questions.update', [$event, $question]), [
            'label' => 'Minat Kegiatan',
            'field_key' => 'minat_kegiatan',
            'type' => EventQuestion::TYPE_RADIO,
            'options_text' => "Pendidikan\nKesehatan",
            'is_required' => '0',
            'is_active' => '1',
            'sort_order' => 3,
        ]);

        $response->assertRedirect(route('admin.events.questions.index', $event))
            ->assertSessionHas('success');

        $question->refresh();

        $this->assertSame('Minat Kegiatan', $question->label);
        $this->assertSame('minat_kegiatan', $question->field_key);
        $this->assertSame(EventQuestion::TYPE_RADIO, $question->type);
        $this->assertSame(['Pendidikan', 'Kesehatan'], $question->options);
        $this->assertFalse($question->is_required);
        $this->assertSame(3, $question->sort_order);
    }

    public function test_update_ignores_its_own_field_key_when_checking_uniqueness(): void
    {
        $event = Event::factory()->create();
        $question = EventQuestion::factory()->for($event)->create(['field_key' => 'motivation']);

        $this->actingAs($this->admin())->put(route('admin.events.questions.update', [$event, $question]), [
            'label' => 'Motivasi',
            'field_key' => 'motivation',
            'type' => EventQuestion::TYPE_TEXTAREA,
            'is_active' => '1',
        ])->assertSessionHasNoErrors();
    }

    public function test_preview_renders_the_public_form_with_active_questions(): void
    {
        $event = Event::factory()->create();
        EventQuestion::factory()->for($event)->create(['label' => 'Pertanyaan Aktif']);
        EventQuestion::factory()->for($event)->create([
            'label' => 'Pertanyaan Nonaktif',
            'field_key' => 'nonaktif',
            'is_active' => false,
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.events.questions.preview', $event))
            ->assertOk()
            ->assertSee('Pertanyaan Aktif')
            ->assertDontSee('Pertanyaan Nonaktif')
            ->assertSee('Preview Formulir Pendaftaran');
    }

    public function test_admin_can_move_a_question_up_and_down(): void
    {
        $event = Event::factory()->create();
        $first = EventQuestion::factory()->for($event)->create(['field_key' => 'pertama', 'sort_order' => 1]);
        $second = EventQuestion::factory()->for($event)->create(['field_key' => 'kedua', 'sort_order' => 2]);

        $this->actingAs($this->admin())
            ->post(route('admin.events.questions.move', [$event, $second]), ['direction' => 'up'])
            ->assertRedirect(route('admin.events.questions.index', $event));

        $this->assertSame(1, $second->fresh()->sort_order);
        $this->assertSame(2, $first->fresh()->sort_order);

        $this->actingAs($this->admin())
            ->post(route('admin.events.questions.move', [$event, $second]), ['direction' => 'down'])
            ->assertRedirect(route('admin.events.questions.index', $event));

        $this->assertSame(2, $second->fresh()->sort_order);
        $this->assertSame(1, $first->fresh()->sort_order);
    }

    public function test_store_persists_safe_length_validation_rules(): void
    {
        $event = Event::factory()->create();

        $this->actingAs($this->admin())->post(route('admin.events.questions.store', $event), [
            'label' => 'Nama Panggilan',
            'type' => EventQuestion::TYPE_TEXT,
            'min_length' => 3,
            'max_length' => 20,
            'is_active' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertSame(['min:3', 'max:20'], EventQuestion::sole()->validation_rules);
    }

    public function test_store_rejects_max_length_smaller_than_min_length(): void
    {
        $event = Event::factory()->create();

        $this->actingAs($this->admin())->post(route('admin.events.questions.store', $event), [
            'label' => 'Nama Panggilan',
            'type' => EventQuestion::TYPE_TEXT,
            'min_length' => 10,
            'max_length' => 5,
            'is_active' => '1',
        ])->assertSessionHasErrors('max_length');

        $this->assertDatabaseCount('event_questions', 0);
    }

    public function test_destroy_removes_question(): void
    {
        $event = Event::factory()->create();
        $question = EventQuestion::factory()->for($event)->create();

        $this->actingAs($this->admin())
            ->delete(route('admin.events.questions.destroy', [$event, $question]))
            ->assertRedirect(route('admin.events.questions.index', $event))
            ->assertSessionHas('success');

        $this->assertModelMissing($question);
    }
}
