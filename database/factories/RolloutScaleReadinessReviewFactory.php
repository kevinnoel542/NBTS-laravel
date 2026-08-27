<?php

namespace Database\Factories;

use App\Models\RolloutPilotReadinessReview;
use App\Models\RolloutScaleReadinessReview;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RolloutScaleReadinessReview>
 */
class RolloutScaleReadinessReviewFactory extends Factory
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
            'candidate_sites' => ['region-a-center-1'],
            'kpi_comparison' => [
                'adoption' => ['baseline' => 0, 'current' => 1],
                'downtime' => ['baseline' => 0, 'current' => 0],
                'expiry' => ['baseline' => 0, 'current' => 0],
                'incident' => ['baseline' => 0, 'current' => 0],
                'request_fill' => ['baseline' => 0, 'current' => 1],
                'safety' => ['baseline' => 0, 'current' => 1],
                'support' => ['baseline' => 0, 'current' => 1],
                'turnaround' => ['baseline' => 0, 'current' => 1],
            ],
            'monitoring_plan' => ['daily_review' => true, 'escalation' => true],
            'operating_budget' => ['infrastructure', 'devices', 'messaging', 'support', 'training'],
            'readiness_criteria' => ['site_assessed', 'users_trained', 'support_ready'],
            'review_reference' => 'RSR-'.$this->faker->unique()->numerify('######'),
            'reviewed_at' => now(),
            'reviewed_by' => User::factory(),
            'rollout_pilot_readiness_review_id' => RolloutPilotReadinessReview::factory(),
            'scale_level' => 'regional',
            'status' => 'blocked',
            'support_model' => ['helpdesk' => true, 'on_call' => true],
            'unresolved_risks' => [],
            'vendor_exit_plan' => ['source_ownership', 'data_exports', 'handover_drill'],
        ];
    }
}
