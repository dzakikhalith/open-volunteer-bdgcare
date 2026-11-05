<?php

namespace Database\Factories;

use App\Models\Registration;
use App\Models\RegistrationStatusHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RegistrationStatusHistory>
 */
class RegistrationStatusHistoryFactory extends Factory
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
            'changed_by' => null,
            'old_status' => Registration::STATUS_PENDING,
            'new_status' => Registration::STATUS_REVIEWED,
            'notes' => null,
        ];
    }
}
