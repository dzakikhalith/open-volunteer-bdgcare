<?php

namespace App\Mail;

use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class RegistrationStatusChanged extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public Registration $registration)
    {
        $this->registration->loadMissing('event');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Update Status Pendaftaran '.$this->registration->registration_code.' - Bandung Care',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.registrations.status-changed',
            with: [
                'registration' => $this->registration,
                'statusLabel' => $this->statusLabel(),
                'statusMessage' => $this->statusMessage(),
                'notes' => $this->registration->statusHistories()->latest()->value('notes'),
                'trackingUrl' => URL::signedRoute('registrations.show', $this->registration),
            ],
        );
    }

    /**
     * Get the user friendly label of the current status.
     */
    private function statusLabel(): string
    {
        return match ($this->registration->status) {
            Registration::STATUS_PENDING => 'Menunggu Review',
            Registration::STATUS_REVIEWED => 'Sedang Direview',
            Registration::STATUS_ACCEPTED => 'Diterima',
            Registration::STATUS_REJECTED => 'Ditolak',
            Registration::STATUS_WITHDRAWN => 'Dibatalkan',
            default => $this->registration->status,
        };
    }

    /**
     * Get the message that explains the current status.
     */
    private function statusMessage(): string
    {
        return match ($this->registration->status) {
            Registration::STATUS_PENDING => 'Pendaftaran Anda sudah kami terima dan sedang menunggu review panitia.',
            Registration::STATUS_REVIEWED => 'Pendaftaran Anda sedang dalam proses review panitia.',
            Registration::STATUS_ACCEPTED => 'Selamat! Anda diterima sebagai volunteer pada event ini. Panitia akan menghubungi Anda melalui email atau telepon.',
            Registration::STATUS_REJECTED => 'Mohon maaf, pendaftaran Anda belum dapat kami terima pada event ini.',
            Registration::STATUS_WITHDRAWN => 'Pendaftaran ini telah dibatalkan.',
            default => 'Status pendaftaran Anda sedang diproses.',
        };
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
