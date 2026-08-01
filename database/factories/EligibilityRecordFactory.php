<?php

namespace Database\Factories;

use App\EligibilityStatus;
use App\Models\EligibilityRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EligibilityRecord>
 */
class EligibilityRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->donor(),
            'checked_by' => User::factory()->staff(),
            'status' => EligibilityStatus::Eligible,
            'age' => fake()->numberBetween(18, 55),
            'weight_kg' => fake()->randomFloat(2, 50, 110),
            'answers' => [],
            'next_eligible_donation_date' => null,
            'notes' => null,
        ];
    }
}
