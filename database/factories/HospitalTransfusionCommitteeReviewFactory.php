<?php

namespace Database\Factories;

use App\Models\Hospital;
use App\Models\HospitalTransfusionCommitteeReview;
use App\Models\User;
use App\QualityAuditStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HospitalTransfusionCommitteeReview>
 */
class HospitalTransfusionCommitteeReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'hospital_id' => Hospital::factory(),
            'chaired_by' => User::factory()->staff(),
            'review_reference' => fake()->unique()->bothify('HTC-########'),
            'meeting_date' => today(),
            'status' => QualityAuditStatus::Planned,
            'utilization_metrics' => [],
            'emergency_release_review' => [],
            'reaction_review' => [],
            'wastage_review' => [],
            'education_actions' => [],
            'linked_deviation_ids' => [],
            'closed_at' => null,
        ];
    }
}
