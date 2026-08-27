<?php

namespace Database\Factories;

use App\Models\RolloutPilotReadinessReview;
use App\Models\RolloutSiteAssessment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RolloutPilotReadinessReview>
 */
class RolloutPilotReadinessReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'approved_at' => null,
            'approved_by' => null,
            'chain_coverage' => [
                'collection',
                'compatibility',
                'components',
                'dispatch_receipt',
                'hospital_request',
                'inventory',
                'laboratory_qc',
                'quarantine_release',
                'screening',
                'specimens',
                'transfusion_outcome',
            ],
            'data_migration_evidence' => ['reconciled' => true],
            'downtime_restore_evidence' => ['restore_test' => true],
            'exit_criteria' => ['critical_defects' => 0],
            'open_defects' => [],
            'pilot_name' => 'Controlled safety pilot',
            'pilot_sites' => ['site-a'],
            'prerequisites' => [
                'approved_policy',
                'hardware_ready',
                'sop_deployed',
                'support_ready',
                'test_environment',
            ],
            'review_reference' => 'RPR-'.$this->faker->unique()->numerify('######'),
            'reviewed_at' => now(),
            'reviewed_by' => User::factory(),
            'rollout_site_assessment_id' => RolloutSiteAssessment::factory(),
            'signoffs' => ['clinical', 'quality', 'operations'],
            'status' => 'blocked',
            'traceability_recall_evidence' => ['simulation_passed' => true],
            'training_evidence' => ['competency_records' => true],
            'validation_evidence' => ['test_suite' => true, 'uat' => true],
        ];
    }
}
