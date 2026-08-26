<?php

namespace Database\Factories;

use App\Models\AccessReview;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AccessReview>
 */
class AccessReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'owner_id' => User::factory()->staff(),
            'approved_by' => User::factory()->staff(),
            'review_reference' => fake()->unique()->bothify('AR-########'),
            'scope' => ['roles' => ['privileged', 'clinical']],
            'high_risk_roles' => ['super_admin', 'laboratory_approver_quality_officer'],
            'conflicts' => [],
            'findings' => ['no_unapproved_access' => true],
            'status' => 'completed',
            'due_at' => now()->addMonth(),
            'completed_at' => now(),
        ];
    }
}
