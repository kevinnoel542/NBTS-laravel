<?php

namespace Database\Factories;

use App\BloodGroup;
use App\Models\BloodCenter;
use App\Models\BloodInventory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BloodInventory>
 */
class BloodInventoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'blood_center_id' => BloodCenter::factory(),
            'blood_group' => BloodGroup::OPositive,
            'available_units' => fake()->numberBetween(0, 25),
            'reserved_units' => fake()->numberBetween(0, 5),
            'minimum_threshold' => 5,
        ];
    }
}
