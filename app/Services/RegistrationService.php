<?php

namespace App\Services;

use App\Exceptions\RegistrationNotOpenException;
use App\Models\Event;
use App\Models\EventQuestion;
use App\Models\Registration;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class RegistrationService
{
    /**
     * Create a volunteer registration and its dynamic answers atomically.
     *
     * The event row is locked so that concurrent requests cannot exceed the
     * volunteer quota or pass the registration window at the same time.
     *
     * @param  array{full_name: string, email: string, phone: string, answers?: array<string, mixed>}  $data
     *
     * @throws RegistrationNotOpenException
     */
    public function register(Event $event, array $data): Registration
    {
        return DB::transaction(function () use ($event, $data): Registration {
            /** @var Event $lockedEvent */
            $lockedEvent = Event::query()
                ->whereKey($event->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $this->ensureRegistrationIsOpen($lockedEvent);

            $registration = $lockedEvent->registrations()->create([
                'registration_code' => Registration::generateUniqueCode(),
                'full_name' => $data['full_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'status' => Registration::STATUS_PENDING,
                'registered_at' => now(),
            ]);

            $this->storeAnswers($registration, $lockedEvent, $data['answers'] ?? []);

            return $registration;
        });
    }

    /**
     * Re-check every registration rule while the event row is locked.
     *
     * @throws RegistrationNotOpenException
     */
    private function ensureRegistrationIsOpen(Event $event): void
    {
        if (! $event->isRegistrationOpen()) {
            if ($event->isQuotaFull()) {
                throw RegistrationNotOpenException::becauseQuotaIsFull();
            }

            throw RegistrationNotOpenException::becauseEventIsClosed();
        }
    }

    /**
     * Persist the answers to the event's dynamic questions.
     *
     * @param  array<string, mixed>  $answers
     */
    private function storeAnswers(Registration $registration, Event $event, array $answers): void
    {
        foreach ($event->activeQuestions()->get() as $question) {
            $value = $answers[$question->field_key] ?? null;

            if ($value === null || $value === '' || $value === []) {
                continue;
            }

            if ($question->type === EventQuestion::TYPE_FILE) {
                if (! $value instanceof UploadedFile) {
                    continue;
                }

                $path = $value->store('registration-files', 'local');

                if ($path === false) {
                    continue;
                }

                $registration->answers()->create([
                    'event_question_id' => $question->getKey(),
                    'question_label_snapshot' => $question->label,
                    'question_key_snapshot' => $question->field_key,
                    'question_type_snapshot' => $question->type,
                    'answer_text' => $path,
                    'answer_json' => null,
                ]);

                continue;
            }

            $registration->answers()->create([
                'event_question_id' => $question->getKey(),
                'question_label_snapshot' => $question->label,
                'question_key_snapshot' => $question->field_key,
                'question_type_snapshot' => $question->type,
                'answer_text' => is_array($value) ? null : (string) $value,
                'answer_json' => is_array($value) ? array_values($value) : null,
            ]);
        }
    }
}
