<?php

namespace Tests\Feature;

use App\Mail\RegistrationStatusChanged;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class RegistrationStatusNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_status_change_sends_email_to_the_registrant(): void
    {
        Mail::fake();

        $registration = Registration::factory()->create([
            'full_name' => 'Siti Aminah',
            'email' => 'siti@example.com',
            'status' => Registration::STATUS_PENDING,
        ]);

        $this->actingAs(User::factory()->admin()->create())
            ->patch(route('admin.registrations.update-status', $registration), [
                'status' => Registration::STATUS_ACCEPTED,
                'notes' => 'Selamat bergabung di kegiatan ini.',
            ])->assertSessionHas('success');

        Mail::assertSent(RegistrationStatusChanged::class, function (RegistrationStatusChanged $mail) use ($registration): bool {
            $body = $mail->render();

            return $mail->hasTo('siti@example.com')
                && $mail->registration->is($registration)
                && $mail->envelope()->subject === 'Update Status Pendaftaran '.$registration->registration_code.' - Bandung Care'
                && str_contains($body, 'Diterima')
                && str_contains($body, 'Selamat bergabung di kegiatan ini.')
                && str_contains($body, URL::signedRoute('registrations.show', $registration));
        });
    }

    public function test_notification_uses_the_new_status_and_event_details(): void
    {
        Mail::fake();

        $registration = Registration::factory()->create([
            'email' => 'siti@example.com',
            'status' => Registration::STATUS_REVIEWED,
        ]);

        $this->actingAs(User::factory()->admin()->create())
            ->patch(route('admin.registrations.update-status', $registration), [
                'status' => Registration::STATUS_REJECTED,
                'notes' => 'Kuota event sudah terpenuhi.',
            ])->assertSessionHas('success');

        Mail::assertSent(RegistrationStatusChanged::class, function (RegistrationStatusChanged $mail) use ($registration): bool {
            $body = $mail->render();

            return str_contains($body, 'Ditolak')
                && str_contains($body, $registration->event->title)
                && str_contains($body, 'Kuota event sudah terpenuhi.');
        });
    }

    public function test_no_email_is_sent_when_status_does_not_change(): void
    {
        Mail::fake();

        $registration = Registration::factory()->create([
            'status' => Registration::STATUS_PENDING,
        ]);

        $this->actingAs(User::factory()->admin()->create())
            ->patch(route('admin.registrations.update-status', $registration), [
                'status' => Registration::STATUS_PENDING,
            ])->assertSessionHas('error');

        Mail::assertNothingSent();
    }
}
