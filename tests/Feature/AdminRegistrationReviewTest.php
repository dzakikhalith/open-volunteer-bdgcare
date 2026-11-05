<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventQuestion;
use App\Models\Registration;
use App\Models\RegistrationAnswer;
use App\Models\RegistrationStatusHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminRegistrationReviewTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $this->get(route('admin.registrations.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_non_admin_user_cannot_review_registrations(): void
    {
        $registration = Registration::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('admin.registrations.show', $registration))
            ->assertForbidden();
    }

    public function test_index_lists_and_filters_registrations(): void
    {
        $event = Event::factory()->create(['title' => 'Bakti Sosial']);
        $otherEvent = Event::factory()->create(['title' => 'Donor Darah']);

        Registration::factory()->for($event)->create([
            'full_name' => 'Siti Aminah',
            'status' => Registration::STATUS_PENDING,
        ]);
        Registration::factory()->for($event)->create([
            'full_name' => 'Budi Santoso',
            'status' => Registration::STATUS_ACCEPTED,
        ]);
        Registration::factory()->for($otherEvent)->create([
            'full_name' => 'Rina Melati',
            'status' => Registration::STATUS_PENDING,
        ]);

        $response = $this->actingAs($this->admin())->get(route('admin.registrations.index', [
            'search' => 'Siti',
            'event' => $event->id,
            'status' => Registration::STATUS_PENDING,
        ]));

        $response->assertOk()
            ->assertSee('Siti Aminah')
            ->assertDontSee('Budi Santoso')
            ->assertDontSee('Rina Melati');
    }

    public function test_index_filters_registrations_by_registration_date(): void
    {
        Registration::factory()->create([
            'full_name' => 'Siti Aminah',
            'registered_at' => '2026-02-10 10:00:00',
        ]);
        Registration::factory()->create([
            'full_name' => 'Budi Santoso',
            'registered_at' => '2026-01-05 10:00:00',
        ]);
        Registration::factory()->create([
            'full_name' => 'Rina Melati',
            'registered_at' => '2026-03-20 10:00:00',
        ]);

        $response = $this->actingAs($this->admin())->get(route('admin.registrations.index', [
            'date_from' => '2026-02-01',
            'date_to' => '2026-02-28',
        ]));

        $response->assertOk()
            ->assertSee('Siti Aminah')
            ->assertDontSee('Budi Santoso')
            ->assertDontSee('Rina Melati');
    }

    public function test_index_rejects_an_invalid_date_range(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.registrations.index', [
                'date_from' => '2026-03-01',
                'date_to' => '2026-02-01',
            ]))
            ->assertSessionHasErrors('date_to');
    }

    public function test_show_displays_registration_details_answers_and_history(): void
    {
        $registration = Registration::factory()->create([
            'full_name' => 'Siti Aminah',
            'email' => 'siti@example.com',
            'phone' => '081234567890',
        ]);
        RegistrationAnswer::factory()->for($registration)->create([
            'question_label_snapshot' => 'Motivasi',
            'answer_text' => 'Ingin membantu masyarakat',
        ]);
        RegistrationStatusHistory::factory()->for($registration)->create([
            'old_status' => Registration::STATUS_PENDING,
            'new_status' => Registration::STATUS_REVIEWED,
            'notes' => 'Berkas sudah lengkap.',
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.registrations.show', $registration))
            ->assertOk()
            ->assertSee($registration->registration_code)
            ->assertSee('Siti Aminah')
            ->assertSee('siti@example.com')
            ->assertSee('081234567890')
            ->assertSee('Ingin membantu masyarakat')
            ->assertSee('Berkas sudah lengkap.');
    }

    public function test_admin_can_update_registration_status_and_record_history(): void
    {
        $admin = $this->admin();
        $registration = Registration::factory()->create([
            'status' => Registration::STATUS_PENDING,
            'reviewed_at' => null,
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.registrations.show', $registration))
            ->patch(route('admin.registrations.update-status', $registration), [
                'status' => Registration::STATUS_ACCEPTED,
                'notes' => 'Selamat bergabung.',
            ]);

        $response->assertRedirect(route('admin.registrations.show', $registration))
            ->assertSessionHas('success');

        $registration->refresh();

        $this->assertSame(Registration::STATUS_ACCEPTED, $registration->status);
        $this->assertNotNull($registration->reviewed_at);

        $history = $registration->statusHistories()->sole();

        $this->assertSame(Registration::STATUS_PENDING, $history->old_status);
        $this->assertSame(Registration::STATUS_ACCEPTED, $history->new_status);
        $this->assertSame('Selamat bergabung.', $history->notes);
        $this->assertSame($admin->id, $history->changed_by);
    }

    public function test_setting_status_to_pending_clears_review_timestamp(): void
    {
        $registration = Registration::factory()->create([
            'status' => Registration::STATUS_REVIEWED,
            'reviewed_at' => now(),
        ]);

        $this->actingAs($this->admin())
            ->patch(route('admin.registrations.update-status', $registration), [
                'status' => Registration::STATUS_PENDING,
            ])->assertSessionHas('success');

        $registration->refresh();

        $this->assertSame(Registration::STATUS_PENDING, $registration->status);
        $this->assertNull($registration->reviewed_at);
    }

    public function test_updating_to_the_same_status_is_rejected_without_history(): void
    {
        $registration = Registration::factory()->create([
            'status' => Registration::STATUS_PENDING,
        ]);

        $this->actingAs($this->admin())
            ->from(route('admin.registrations.show', $registration))
            ->patch(route('admin.registrations.update-status', $registration), [
                'status' => Registration::STATUS_PENDING,
            ])
            ->assertRedirect(route('admin.registrations.show', $registration))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('registration_status_histories', 0);
    }

    public function test_invalid_status_is_rejected(): void
    {
        $registration = Registration::factory()->create();

        $this->actingAs($this->admin())
            ->patch(route('admin.registrations.update-status', $registration), [
                'status' => 'UNKNOWN',
            ])
            ->assertSessionHasErrors('status');

        $this->assertSame(Registration::STATUS_PENDING, $registration->fresh()->status);
        $this->assertDatabaseCount('registration_status_histories', 0);
    }

    public function test_admin_can_download_a_file_answer(): void
    {
        Storage::fake('local');

        $registration = Registration::factory()->create();
        Storage::disk('local')->put('registration-files/cv.pdf', 'isi berkas');

        $answer = RegistrationAnswer::factory()->for($registration)->create([
            'question_label_snapshot' => 'Berkas CV',
            'question_type_snapshot' => EventQuestion::TYPE_FILE,
            'answer_text' => 'registration-files/cv.pdf',
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.registrations.answers.download', [$registration, $answer]))
            ->assertOk()
            ->assertDownload('berkas-cv.pdf');
    }

    public function test_guest_cannot_download_a_file_answer(): void
    {
        $registration = Registration::factory()->create();
        $answer = RegistrationAnswer::factory()->for($registration)->create([
            'question_type_snapshot' => EventQuestion::TYPE_FILE,
            'answer_text' => 'registration-files/cv.pdf',
        ]);

        $this->get(route('admin.registrations.answers.download', [$registration, $answer]))
            ->assertRedirect(route('admin.login'));
    }

    public function test_non_admin_cannot_download_a_file_answer(): void
    {
        $registration = Registration::factory()->create();
        $answer = RegistrationAnswer::factory()->for($registration)->create([
            'question_type_snapshot' => EventQuestion::TYPE_FILE,
            'answer_text' => 'registration-files/cv.pdf',
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.registrations.answers.download', [$registration, $answer]))
            ->assertForbidden();
    }

    public function test_file_answer_from_another_registration_returns_not_found(): void
    {
        Storage::fake('local');

        $registration = Registration::factory()->create();
        $otherRegistration = Registration::factory()->create();
        Storage::disk('local')->put('registration-files/cv.pdf', 'isi berkas');

        $answer = RegistrationAnswer::factory()->for($otherRegistration)->create([
            'question_type_snapshot' => EventQuestion::TYPE_FILE,
            'answer_text' => 'registration-files/cv.pdf',
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.registrations.answers.download', [$registration, $answer]))
            ->assertNotFound();
    }

    public function test_non_file_answer_cannot_be_downloaded(): void
    {
        $registration = Registration::factory()->create();
        $answer = RegistrationAnswer::factory()->for($registration)->create([
            'question_type_snapshot' => EventQuestion::TYPE_TEXTAREA,
            'answer_text' => 'Jawaban teks biasa',
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.registrations.answers.download', [$registration, $answer]))
            ->assertNotFound();
    }

    public function test_notes_cannot_exceed_length_limit(): void
    {
        $registration = Registration::factory()->create();

        $this->actingAs($this->admin())
            ->patch(route('admin.registrations.update-status', $registration), [
                'status' => Registration::STATUS_REJECTED,
                'notes' => str_repeat('a', 1001),
            ])
            ->assertSessionHasErrors('notes');

        $this->assertDatabaseCount('registration_status_histories', 0);
    }
}
