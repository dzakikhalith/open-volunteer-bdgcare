<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Registration;
use App\Models\RegistrationAnswer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationExportTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $this->get(route('admin.registrations.export'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_non_admin_user_cannot_export_registrations(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.registrations.export'))
            ->assertForbidden();
    }

    public function test_export_streams_csv_with_registration_data_and_answers(): void
    {
        $event = Event::factory()->create(['title' => 'Bakti Sosial Bandung']);
        $registration = Registration::factory()->for($event)->create([
            'full_name' => 'Siti Aminah',
            'email' => 'siti@example.com',
            'phone' => '081234567890',
            'status' => Registration::STATUS_ACCEPTED,
            'registered_at' => '2026-01-15 10:00:00',
        ]);
        RegistrationAnswer::factory()->for($registration)->create([
            'question_label_snapshot' => 'Motivasi',
            'answer_text' => 'Ingin membantu masyarakat',
        ]);

        $response = $this->actingAs($this->admin())
            ->get(route('admin.registrations.export'));

        $response->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $this->assertStringContainsString(
            'attachment; filename=pendaftar-bandung-care-',
            (string) $response->headers->get('content-disposition')
        );

        $content = $response->streamedContent();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        $this->assertStringContainsString('Kode Pendaftaran', $content);
        $this->assertStringContainsString($registration->registration_code, $content);
        $this->assertStringContainsString('Siti Aminah', $content);
        $this->assertStringContainsString('siti@example.com', $content);
        $this->assertStringContainsString('081234567890', $content);
        $this->assertStringContainsString('Bakti Sosial Bandung', $content);
        $this->assertStringContainsString('ACCEPTED', $content);
        $this->assertStringContainsString('Motivasi: Ingin membantu masyarakat', $content);
    }

    public function test_export_neutralizes_spreadsheet_formulas(): void
    {
        $registration = Registration::factory()->create([
            'full_name' => '=HYPERLINK("https://example.com")',
            'email' => 'siti@example.com',
        ]);
        RegistrationAnswer::factory()->for($registration)->create([
            'question_label_snapshot' => '=Catatan',
            'answer_text' => 'aman',
        ]);

        $content = $this->actingAs($this->admin())
            ->get(route('admin.registrations.export'))
            ->streamedContent();

        $this->assertStringContainsString("'=HYPERLINK", $content);
        $this->assertStringContainsString("'=Catatan: aman", $content);
        $this->assertStringNotContainsString(',=HYPERLINK', $content);
    }

    public function test_export_applies_the_same_filters_as_the_registration_list(): void
    {
        $event = Event::factory()->create();
        $otherEvent = Event::factory()->create();

        Registration::factory()->for($event)->create([
            'full_name' => 'Siti Aminah',
            'email' => 'siti@example.com',
            'status' => Registration::STATUS_ACCEPTED,
        ]);
        Registration::factory()->for($event)->create([
            'full_name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'status' => Registration::STATUS_PENDING,
        ]);
        Registration::factory()->for($otherEvent)->create([
            'full_name' => 'Rina Melati',
            'email' => 'rina@example.com',
            'status' => Registration::STATUS_ACCEPTED,
        ]);

        $content = $this->actingAs($this->admin())
            ->get(route('admin.registrations.export', [
                'search' => 'Siti',
                'event' => $event->id,
                'status' => Registration::STATUS_ACCEPTED,
            ]))
            ->streamedContent();

        $this->assertStringContainsString('Siti Aminah', $content);
        $this->assertStringNotContainsString('Budi Santoso', $content);
        $this->assertStringNotContainsString('Rina Melati', $content);
    }

    public function test_export_filters_registrations_by_registration_date(): void
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

        $content = $this->actingAs($this->admin())
            ->get(route('admin.registrations.export', [
                'date_from' => '2026-02-01',
                'date_to' => '2026-02-28',
            ]))
            ->streamedContent();

        $this->assertStringContainsString('Siti Aminah', $content);
        $this->assertStringNotContainsString('Budi Santoso', $content);
        $this->assertStringNotContainsString('Rina Melati', $content);
    }

    public function test_export_rejects_an_invalid_date_range(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.registrations.export', [
                'date_from' => '2026-03-01',
                'date_to' => '2026-02-01',
            ]))
            ->assertSessionHasErrors('date_to');
    }
}
