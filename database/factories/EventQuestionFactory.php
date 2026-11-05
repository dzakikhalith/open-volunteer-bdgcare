<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventQuestion>
 */
class EventQuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'label' => 'Motivasi Mengikuti Kegiatan',
            'field_key' => 'motivation',
            'type' => EventQuestion::TYPE_TEXTAREA,
            'placeholder' => 'Tuliskan motivasi Anda',
            'help_text' => null,
            'is_required' => true,
            'options' => null,
            'validation_rules' => null,
            'sort_order' => 0,
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the question is a select field.
     *
     * @param  list<string>  $options
     */
    public function select(array $options): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => EventQuestion::TYPE_SELECT,
            'options' => $options,
        ]);
    }

    /**
     * Indicate that the question is a checkbox group.
     *
     * @param  list<string>  $options
     */
    public function checkbox(array $options): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => EventQuestion::TYPE_CHECKBOX,
            'options' => $options,
        ]);
    }
}
