<?php

namespace Database\Factories;

use App\EqaAssessmentStatus;
use App\Models\BloodCenter;
use App\Models\EqaAssessment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EqaAssessment>
 */
class EqaAssessmentFactory extends Factory
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
            'laboratory_test_catalog_id' => null,
            'submitted_by' => null,
            'reviewed_by' => null,
            'scheme_code' => fake()->unique()->bothify('EQA-####'),
            'round_code' => 'ROUND-1',
            'status' => EqaAssessmentStatus::Scheduled,
            'expected_results' => [],
            'submitted_results' => null,
            'findings' => null,
            'linked_deviation_ids' => [],
            'due_at' => now()->addWeek(),
            'submitted_at' => null,
            'reviewed_at' => null,
        ];
    }
}
