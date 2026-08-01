<?php

namespace Database\Factories;

use App\BloodGroup;
use App\BloodUnitStatus;
use App\Models\BloodCenter;
use App\Models\BloodUnit;
use App\Models\Donation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BloodUnit>
 */
class BloodUnitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'unit_number' => fake()->unique()->numerify('UNT-########'),
            'donation_id' => Donation::factory(),
            'donor_id' => User::factory()->donor(),
            'blood_center_id' => BloodCenter::factory(),
            'blood_group' => BloodGroup::OPositive,
            'collection_date' => today(),
            'expiry_date' => today()->addDays(35),
            'status' => BloodUnitStatus::Collected,
            'current_location' => 'Collection laboratory',
            'handled_by' => User::factory()->staff(),
        ];
    }
}
