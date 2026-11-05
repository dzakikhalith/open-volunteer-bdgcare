<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->randomElement(['Pendidikan', 'Lingkungan', 'Kemanusiaan', 'Kesehatan', 'Pengabdian Masyarakat', 'Donasi dan Sosial']);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.$this->faker->unique()->numberBetween(100, 999),
            'description' => $this->faker->sentence(10),
        ];
    }
}
