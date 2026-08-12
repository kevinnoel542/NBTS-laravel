<?php

namespace Database\Factories;

use App\DonorDuplicateCaseStatus;
use App\Models\BloodCenter;
use App\Models\DonorDuplicateCase;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<DonorDuplicateCase> */
class DonorDuplicateCaseFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'primary_donor_id' => User::factory()->donor(),
            'candidate_donor_id' => User::factory()->donor(),
            'blood_center_id' => BloodCenter::factory(),
            'status' => DonorDuplicateCaseStatus::Pending,
            'match_signals' => ['name' => true, 'date_of_birth' => true],
            'match_score' => 80,
            'detected_by' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'review_reason' => null,
        ];
    }
}
