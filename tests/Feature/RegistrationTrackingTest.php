<?php

namespace Tests\Feature;

use App\Models\Registration;
use App\Models\RegistrationAnswer;
use App\Models\RegistrationStatusHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class RegistrationTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_tracking_form_renders(): void
    {
        $response = $this->get(route('registrations.track'));

        $response->assertOk()
            ->assertSee('Cek Status Pendaftaran')
            ->assertSee('Kode Pendaftaran');
    }

    public function test_status_page_renders_minimum_registration_information(): void
    {
        $registration = Registration::factory()->create([
            'full_name' => 'Siti Aminah',
            'email' => 'siti@example.com',
            'phone' => '081234567890',
        ]);

        $response = $this->get(URL::signedRoute('registrations.show', $registration));

        $response->assertOk()
            ->assertSee($registration->registration_code)
            ->assertSee('Siti Aminah')
            ->assertSee($registration->event->title)
            ->assertSee('Menunggu Review')
            ->assertDontSee('siti@example.com')
            ->assertDontSee('081234567890');
    }

    public function test_status_page_lists_status_history_without_internal_notes(): void
    {
        $registration = Registration::factory()->create([
            'status' => Registration::STATUS_ACCEPTED,
        ]);
        RegistrationStatusHistory::factory()->for($registration)->create([
            'old_status' => Registration::STATUS_REVIEWED,
            'new_status' => Registration::STATUS_ACCEPTED,
            'notes' => 'Catatan internal panitia.',
        ]);

        $response = $this->get(URL::signedRoute('registrations.show', $registration));

        $response->assertOk()
            ->assertSee('Diterima')
            ->assertDontSee('Catatan internal panitia.');
    }

    public function test_status_page_does_not_expose_form_answers(): void
    {
        $registration = Registration::factory()->create();
        RegistrationAnswer::factory()->for($registration)->create([
            'question_label_snapshot' => 'Motivasi',
            'answer_text' => 'Ingin membantu masyarakat',
        ]);

        $response = $this->get(URL::signedRoute('registrations.show', $registration));

        $response->assertOk()
            ->assertDontSee('Motivasi')
            ->assertDontSee('Ingin membantu masyarakat');
    }

    public function test_status_page_escapes_user_supplied_data(): void
    {
        $registration = Registration::factory()->create([
            'full_name' => '<script>alert(1)</script>',
        ]);

        $response = $this->get(URL::signedRoute('registrations.show', $registration));

        $response->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_status_page_returns_404_for_unknown_signed_route(): void
    {
        $response = $this->get(URL::signedRoute('registrations.show', ['registration' => 'BC-000000-XXXXX']));

        $response->assertNotFound();
    }

    public function test_status_page_rejects_an_unsigned_url(): void
    {
        $registration = Registration::factory()->create();

        $this->get(route('registrations.show', $registration))
            ->assertForbidden();
    }

    public function test_lookup_redirects_to_a_signed_status_page_with_normalized_code(): void
    {
        $registration = Registration::factory()->create();

        $response = $this->post(route('registrations.lookup'), [
            'registration_code' => strtolower($registration->registration_code),
        ]);

        $response->assertRedirect(URL::signedRoute('registrations.show', $registration));
    }

    public function test_lookup_returns_error_for_unknown_code(): void
    {
        $response = $this->post(route('registrations.lookup'), [
            'registration_code' => 'BC-000000-XXXXX',
        ]);

        $response->assertSessionHasErrors([
            'registration_code' => 'Kode pendaftaran tidak ditemukan.',
        ]);
    }

    public function test_lookup_requires_registration_code(): void
    {
        $response = $this->post(route('registrations.lookup'), []);

        $response->assertSessionHasErrors('registration_code');
    }
}
