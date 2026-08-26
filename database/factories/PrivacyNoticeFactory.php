<?php

namespace Database\Factories;

use App\Models\PrivacyNotice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PrivacyNotice>
 */
class PrivacyNoticeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'approved_by' => User::factory()->staff(),
            'notice_code' => fake()->unique()->bothify('PN-####'),
            'version' => 1,
            'title' => 'NBTS donor privacy notice',
            'channels' => ['web', 'mobile', 'assisted_registration'],
            'consent_scope' => ['donor_contact', 'appointment_reminders', 'eligibility_follow_up'],
            'communication_preferences' => ['sms' => true, 'email' => true, 'push' => true],
            'status' => 'effective',
            'effective_from' => today(),
            'retired_at' => null,
            'approved_at' => now(),
        ];
    }
}
