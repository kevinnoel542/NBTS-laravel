<?php

namespace Database\Factories;

use App\Models\BloodCenter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BloodCenter>
 */
class BloodCenterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company().' Blood Centre',
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'phone' => fake()->unique()->e164PhoneNumber(),
            'email' => fake()->unique()->companyEmail(),
            'opening_hours' => 'Monday–Friday, 08:00–17:00',
            'services' => ['Whole blood donation', 'Donor screening'],
            'capacity_label' => 'Standard capacity',
            'estimated_wait_minutes' => fake()->numberBetween(5, 45),
            'center_type' => 'regional',
            'latitude' => fake()->latitude(-11.7, -1.0),
            'longitude' => fake()->longitude(29.3, 40.5),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }
}
