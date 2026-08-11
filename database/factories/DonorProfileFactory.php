<?php

namespace Database\Factories;

use App\BloodGroupStatus;
use App\EligibilityStatus;
use App\Models\BloodCenter;
use App\Models\DonorProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DonorProfile>
 */
class DonorProfileFactory extends Factory
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
            'preferred_center_id' => null,
            'donor_id' => fake()->unique()->numerify('DNR-########'),
            'blood_group_status' => BloodGroupStatus::Unknown,
            'blood_group_verified' => false,
            'blood_group_verified_at' => null,
            'blood_group_verified_by' => null,
            'next_eligible_donation_date' => null,
            'eligibility_status' => EligibilityStatus::Eligible,
            'last_eligibility_checked_at' => null,
            'eligibility_notes' => null,
            'emergency_contact_name' => fake()->name(),
            'emergency_contact_phone' => fake()->e164PhoneNumber(),
            'push_notifications_enabled' => true,
            'email_notifications_enabled' => true,
            'sms_reminders_enabled' => true,
            'share_anonymized_data' => false,
            'language' => 'en',
            'total_donations' => 0,
            'loyalty_points' => 0,
            'loyalty_tier' => 'Pending',
        ];
    }

    public function withPreferredCenter(): static
    {
        return $this->state(fn (array $attributes): array => [
            'preferred_center_id' => BloodCenter::factory(),
        ]);
    }
}
