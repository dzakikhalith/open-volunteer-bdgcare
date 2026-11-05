<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->unique()->sentence(4);

        return [
            'category_id' => Category::factory(),
            'created_by' => User::where('role', 'admin')->first()?->id,
            'title' => $title,
            'slug' => Str::slug($title),
            'summary' => $this->faker->sentence(15),
            'description' => $this->faker->paragraphs(4, true),
            'location' => 'Bandung, '.$this->faker->streetName(),
            'event_start_at' => now()->addDays(7),
            'event_end_at' => now()->addDays(7)->addHours(4),
            'registration_start_at' => now()->subDays(1),
            'registration_end_at' => now()->addDays(5),
            'quota' => 50,
            'requirements' => "- Bersedia mengikuti kegiatan\n- Memiliki komitmen\n- Usia minimal 17 tahun",
            'recruitment_status' => 'OPEN',
            'is_published' => true,
        ];
    }

    /**
     * Indicate that the event is not accepting registrations.
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'recruitment_status' => 'CLOSED',
        ]);
    }

    /**
     * Indicate that the event is not visible on the public site.
     */
    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
        ]);
    }
}
