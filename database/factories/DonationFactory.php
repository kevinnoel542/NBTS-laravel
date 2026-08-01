<?php

namespace Database\Factories;

use App\BloodGroup;
use App\DonationStatus;
use App\DonationType;
use App\Models\BloodCenter;
use App\Models\Donation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Donation>
 */
class DonationFactory extends Factory
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
            'blood_center_id' => BloodCenter::factory(),
            'recorded_by' => User::factory()->staff(),
            'appointment_id' => null,
            'donation_type' => DonationType::WalkIn,
            'blood_group' => BloodGroup::OPositive,
            'blood_group_verified' => true,
            'volume_ml' => 450,
            'donation_date' => today(),
            'status' => DonationStatus::Completed,
            'notes' => null,
        ];
    }
}
