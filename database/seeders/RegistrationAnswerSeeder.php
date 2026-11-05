<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Registration;
use Illuminate\Database\Seeder;

class RegistrationAnswerSeeder extends Seeder
{
    /**
     * Seed answers for the demo registrations.
     */
    public function run(): void
    {
        $this->call([RegistrationSeeder::class, EventQuestionSeeder::class]);

        $values = [
            'motivation' => 'Ingin ikut berkontribusi dan belajar bersama masyarakat Bandung.',
            'experience' => 'Pernah menjadi panitia kegiatan sosial di kampus.',
            'shirt_size' => 'M',
            'interests' => ['Membaca', 'Seni'],
            'transportation' => 'Bersama komunitas',
            'availability' => 'Pagi hingga siang',
            'blood_type' => 'O',
        ];

        $events = Event::query()
            ->whereIn('slug', EventSeeder::DEMO_SLUGS)
            ->with(['activeQuestions', 'registrations'])
            ->get();

        foreach ($events as $event) {
            foreach ($event->registrations as $registration) {
                $this->seedAnswers($registration, $event, $values);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $values
     */
    private function seedAnswers(Registration $registration, Event $event, array $values): void
    {
        foreach ($event->activeQuestions as $question) {
            if (! array_key_exists($question->field_key, $values)) {
                continue;
            }

            $value = $values[$question->field_key];

            $registration->answers()->updateOrCreate(
                ['event_question_id' => $question->getKey()],
                [
                    'question_label_snapshot' => $question->label,
                    'question_key_snapshot' => $question->field_key,
                    'question_type_snapshot' => $question->type,
                    'answer_text' => is_array($value) ? null : (string) $value,
                    'answer_json' => is_array($value) ? $value : null,
                ],
            );
        }
    }
}
