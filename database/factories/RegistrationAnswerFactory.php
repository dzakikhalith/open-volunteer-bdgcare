<?php

namespace Database\Factories;

use App\Models\EventQuestion;
use App\Models\Registration;
use App\Models\RegistrationAnswer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RegistrationAnswer>
 */
class RegistrationAnswerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'registration_id' => Registration::factory(),
            'event_question_id' => null,
            'question_label_snapshot' => 'Motivasi Mengikuti Kegiatan',
            'question_key_snapshot' => 'motivation',
            'question_type_snapshot' => EventQuestion::TYPE_TEXTAREA,
            'answer_text' => $this->faker->sentence(),
            'answer_json' => null,
        ];
    }
}
