<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventQuestion;
use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EventRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_form_renders_event_and_dynamic_questions(): void
    {
        $event = Event::factory()->create();
        EventQuestion::factory()->for($event)->create(['label' => 'Alasan Bergabung']);
        EventQuestion::factory()->for($event)->create([
            'label' => 'Ukuran Kaos',
            'field_key' => 'shirt_size',
            'type' => EventQuestion::TYPE_SELECT,
            'options' => ['S', 'M', 'L'],
            'is_required' => false,
        ]);

        $response = $this->get(route('events.register', $event));

        $response->assertOk()
            ->assertSee($event->title)
            ->assertSee('Alasan Bergabung')
            ->assertSee('Ukuran Kaos');
    }

    public function test_registration_form_redirects_when_event_is_closed(): void
    {
        $event = Event::factory()->closed()->create();

        $response = $this->get(route('events.register', $event));

        $response->assertRedirect(route('events.show', $event))
            ->assertSessionHas('error');
    }

    public function test_registration_form_returns_404_when_event_is_not_published(): void
    {
        $event = Event::factory()->unpublished()->create();

        $response = $this->get(route('events.register', $event));

        $response->assertNotFound();
    }

    public function test_event_page_links_to_registration_when_registration_is_open(): void
    {
        $event = Event::factory()->create();

        $response = $this->get(route('events.show', $event));

        $response->assertOk()
            ->assertSee(route('events.register', $event));
    }

    public function test_event_page_hides_registration_link_when_event_is_closed(): void
    {
        $event = Event::factory()->closed()->create();

        $response = $this->get(route('events.show', $event));

        $response->assertOk()
            ->assertDontSee(route('events.register', $event))
            ->assertSee('Pendaftaran belum dibuka atau sudah ditutup');
    }

    public function test_valid_submission_creates_registration_with_answers(): void
    {
        $this->travelTo('2026-01-15 10:00:00');
        $event = Event::factory()->create(['quota' => 10]);
        $question = EventQuestion::factory()->for($event)->create([
            'label' => 'Motivasi',
            'field_key' => 'motivation',
        ]);

        $response = $this->post(route('events.register.store', $event), [
            'full_name' => '  Siti Aminah ',
            'email' => 'SITI@EXAMPLE.COM',
            'phone' => '0812 3456 7890',
            'answers' => ['motivation' => 'Ingin berkontribusi'],
        ]);

        $registration = Registration::sole();
        $response->assertRedirect(URL::signedRoute('registrations.show', $registration))
            ->assertSessionHas('success');

        $this->assertMatchesRegularExpression('/^BC-260115-[A-Z0-9]{5}$/', $registration->registration_code);
        $this->assertSame('Siti Aminah', $registration->full_name);
        $this->assertSame('siti@example.com', $registration->email);
        $this->assertSame('0812 3456 7890', $registration->phone);
        $this->assertSame(Registration::STATUS_PENDING, $registration->status);
        $this->assertSame($event->id, $registration->event_id);
        $this->assertSame('2026-01-15 10:00:00', $registration->registered_at->format('Y-m-d H:i:s'));

        $answer = $registration->answers()->sole();
        $this->assertSame($question->id, $answer->event_question_id);
        $this->assertSame('Motivasi', $answer->question_label_snapshot);
        $this->assertSame('motivation', $answer->question_key_snapshot);
        $this->assertSame(EventQuestion::TYPE_TEXTAREA, $answer->question_type_snapshot);
        $this->assertSame('Ingin berkontribusi', $answer->answer_text);
        $this->assertNull($answer->answer_json);
    }

    public function test_valid_submission_stores_checkbox_answers_as_json(): void
    {
        $event = Event::factory()->create();
        EventQuestion::factory()->for($event)->checkbox(['Pendidikan', 'Lingkungan', 'Kesehatan'])
            ->create(['field_key' => 'interests']);

        $this->post(route('events.register.store', $event), [
            'full_name' => 'Siti Aminah',
            'email' => 'siti@example.com',
            'phone' => '081234567890',
            'answers' => ['interests' => ['Pendidikan', 'Kesehatan']],
        ])->assertSessionHasNoErrors();

        $answer = Registration::sole()->answers()->sole();

        $this->assertSame(['Pendidikan', 'Kesehatan'], $answer->answer_json);
        $this->assertNull($answer->answer_text);
    }

    public function test_submission_is_rejected_when_required_question_is_missing(): void
    {
        $event = Event::factory()->create();
        EventQuestion::factory()->for($event)->create(['field_key' => 'motivation']);

        $response = $this->post(route('events.register.store', $event), [
            'full_name' => 'Siti Aminah',
            'email' => 'siti@example.com',
            'phone' => '081234567890',
        ]);

        $response->assertSessionHasErrors('answers.motivation');
        $this->assertDatabaseCount('registrations', 0);
    }

    public function test_submission_is_rejected_for_option_outside_available_choices(): void
    {
        $event = Event::factory()->create();
        EventQuestion::factory()->for($event)->select(['S', 'M'])->create([
            'field_key' => 'shirt_size',
            'is_required' => true,
        ]);

        $response = $this->post(route('events.register.store', $event), [
            'full_name' => 'Siti Aminah',
            'email' => 'siti@example.com',
            'phone' => '081234567890',
            'answers' => ['shirt_size' => 'XL'],
        ]);

        $response->assertSessionHasErrors('answers.shirt_size');
        $this->assertDatabaseCount('registrations', 0);
    }

    public function test_submission_is_rejected_for_duplicate_email_on_same_event(): void
    {
        $event = Event::factory()->create();
        Registration::factory()->for($event)->create(['email' => 'siti@example.com']);

        $response = $this->post(route('events.register.store', $event), [
            'full_name' => 'Siti Aminah',
            'email' => 'SITI@example.com',
            'phone' => '081234567890',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseCount('registrations', 1);
    }

    public function test_submission_is_rejected_when_event_is_closed(): void
    {
        $event = Event::factory()->closed()->create();

        $response = $this->post(route('events.register.store', $event), [
            'full_name' => 'Siti Aminah',
            'email' => 'siti@example.com',
            'phone' => '081234567890',
        ]);

        $response->assertRedirect(route('events.show', $event))
            ->assertSessionHas('error');
        $this->assertDatabaseCount('registrations', 0);
    }

    public function test_submission_is_rejected_when_registration_window_has_ended(): void
    {
        $event = Event::factory()->create([
            'registration_end_at' => now()->subDay(),
        ]);

        $response = $this->post(route('events.register.store', $event), [
            'full_name' => 'Siti Aminah',
            'email' => 'siti@example.com',
            'phone' => '081234567890',
        ]);

        $response->assertRedirect(route('events.show', $event))
            ->assertSessionHas('error');
        $this->assertDatabaseCount('registrations', 0);
    }

    public function test_submission_is_rejected_when_quota_is_full(): void
    {
        $event = Event::factory()->create(['quota' => 1]);
        Registration::factory()->for($event)->create();

        $response = $this->post(route('events.register.store', $event), [
            'full_name' => 'Siti Aminah',
            'email' => 'siti@example.com',
            'phone' => '081234567890',
        ]);

        $response->assertRedirect(route('events.show', $event))
            ->assertSessionHas('error');
        $this->assertDatabaseCount('registrations', 1);
    }

    public function test_submission_is_rejected_when_answer_is_shorter_than_the_configured_minimum(): void
    {
        $event = Event::factory()->create();
        EventQuestion::factory()->for($event)->create([
            'field_key' => 'nickname',
            'type' => EventQuestion::TYPE_TEXT,
            'validation_rules' => ['min:5'],
            'is_required' => true,
        ]);

        $this->post(route('events.register.store', $event), [
            'full_name' => 'Siti Aminah',
            'email' => 'siti@example.com',
            'phone' => '081234567890',
            'answers' => ['nickname' => 'Siti'],
        ])->assertSessionHasErrors('answers.nickname');

        $this->assertDatabaseCount('registrations', 0);
    }

    public function test_url_question_requires_a_valid_url(): void
    {
        $event = Event::factory()->create();
        EventQuestion::factory()->for($event)->create([
            'field_key' => 'portfolio',
            'type' => EventQuestion::TYPE_URL,
            'is_required' => true,
        ]);

        $this->post(route('events.register.store', $event), [
            'full_name' => 'Siti Aminah',
            'email' => 'siti@example.com',
            'phone' => '081234567890',
            'answers' => ['portfolio' => 'bukan-url'],
        ])->assertSessionHasErrors('answers.portfolio');

        $this->post(route('events.register.store', $event), [
            'full_name' => 'Siti Aminah',
            'email' => 'siti@example.com',
            'phone' => '081234567890',
            'answers' => ['portfolio' => 'https://example.com/siti'],
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('registrations', 1);
    }

    public function test_file_question_stores_the_upload_on_the_private_disk(): void
    {
        Storage::fake('local');

        $event = Event::factory()->create();
        $question = EventQuestion::factory()->for($event)->create([
            'label' => 'Berkas CV',
            'field_key' => 'cv',
            'type' => EventQuestion::TYPE_FILE,
            'is_required' => true,
        ]);

        $this->post(route('events.register.store', $event), [
            'full_name' => 'Siti Aminah',
            'email' => 'siti@example.com',
            'phone' => '081234567890',
            'answers' => ['cv' => UploadedFile::fake()->create('cv.pdf', 200, 'application/pdf')],
        ])->assertSessionHasNoErrors();

        $answer = Registration::sole()->answers()->sole();

        $this->assertSame($question->id, $answer->event_question_id);
        $this->assertSame(EventQuestion::TYPE_FILE, $answer->question_type_snapshot);
        $this->assertStringStartsWith('registration-files/', $answer->answer_text);
        $this->assertNull($answer->answer_json);
        Storage::disk('local')->assertExists($answer->answer_text);
        Storage::disk('public')->assertMissing($answer->answer_text);
    }

    public function test_file_question_rejects_an_unsupported_file_type(): void
    {
        Storage::fake('local');

        $event = Event::factory()->create();
        EventQuestion::factory()->for($event)->create([
            'field_key' => 'cv',
            'type' => EventQuestion::TYPE_FILE,
            'is_required' => true,
        ]);

        $this->post(route('events.register.store', $event), [
            'full_name' => 'Siti Aminah',
            'email' => 'siti@example.com',
            'phone' => '081234567890',
            'answers' => ['cv' => UploadedFile::fake()->create('shell.php', 10, 'text/x-php')],
        ])->assertSessionHasErrors('answers.cv');

        $this->assertDatabaseCount('registrations', 0);
        $this->assertDatabaseCount('registration_answers', 0);
    }

    public function test_file_question_is_required_when_configured_as_required(): void
    {
        $event = Event::factory()->create();
        EventQuestion::factory()->for($event)->create([
            'field_key' => 'cv',
            'type' => EventQuestion::TYPE_FILE,
            'is_required' => true,
        ]);

        $this->post(route('events.register.store', $event), [
            'full_name' => 'Siti Aminah',
            'email' => 'siti@example.com',
            'phone' => '081234567890',
        ])->assertSessionHasErrors('answers.cv');

        $this->assertDatabaseCount('registrations', 0);
    }

    public function test_inactive_questions_are_not_rendered_or_required(): void
    {
        $event = Event::factory()->create();
        EventQuestion::factory()->for($event)->create([
            'label' => 'Pertanyaan Lama',
            'field_key' => 'legacy_question',
            'is_active' => false,
        ]);

        $this->get(route('events.register', $event))
            ->assertOk()
            ->assertDontSee('Pertanyaan Lama');

        $this->post(route('events.register.store', $event), [
            'full_name' => 'Siti Aminah',
            'email' => 'siti@example.com',
            'phone' => '081234567890',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('registrations', 1);
        $this->assertDatabaseCount('registration_answers', 0);
    }
}
