<?php

namespace Database\Factories;

use App\Models\RolloutPolicyDecision;
use App\Models\RolloutSiteAssessment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RolloutPolicyDecision>
 */
class RolloutPolicyDecisionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'approval_evidence' => null,
            'approved_at' => null,
            'approved_by' => null,
            'category' => 'identifier',
            'decision_code' => 'RPD-'.$this->faker->unique()->numerify('######'),
            'decision_summary' => 'Pending approved rollout policy decision.',
            'due_at' => now()->addMonth(),
            'implementation_controls' => ['change_control', 'training', 'rollback'],
            'options_considered' => ['current_state', 'target_state'],
            'owner_id' => User::factory(),
            'required_approvals' => ['operations', 'clinical', 'quality'],
            'review_schedule' => ['frequency' => 'monthly'],
            'risk_acceptance' => null,
            'rollout_site_assessment_id' => RolloutSiteAssessment::factory(),
            'status' => 'pending',
            'title' => 'Rollout policy decision',
        ];
    }
}
