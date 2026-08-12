<?php

namespace Database\Factories;

use App\DonorIdentityCheckStatus;
use App\DonorIdentityMethod;
use App\Models\BloodCenter;
use App\Models\DonorIdentityCheck;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<DonorIdentityCheck> */
class DonorIdentityCheckFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'donor_id' => User::factory()->donor(),
            'blood_center_id' => BloodCenter::factory(),
            'appointment_id' => null,
            'method' => DonorIdentityMethod::DonorId,
            'reference_suffix' => fake()->numerify('####'),
            'status' => DonorIdentityCheckStatus::Confirmed,
            'confirmed_by' => User::factory()->staff(),
            'confirmed_at' => now(),
            'expires_at' => now()->addHours(12),
            'source_mode' => 'online',
            'failure_reason' => null,
        ];
    }
}
