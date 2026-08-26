<?php

namespace Database\Factories;

use App\Models\RetentionPolicy;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RetentionPolicy>
 */
class RetentionPolicyFactory extends Factory
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
            'record_category' => fake()->unique()->randomElement(['donor_traceability', 'laboratory_result', 'recipient_traceability']).'-'.fake()->unique()->numberBetween(100, 999),
            'retention_period_days' => 3650,
            'archival_after_days' => 1095,
            'legal_basis' => 'approved_nbts_retention_policy',
            'secure_archive_controls' => ['encrypted_archive', 'access_review_required'],
            'deletion_restricted' => true,
            'status' => 'effective',
            'effective_from' => today(),
            'approved_at' => now(),
        ];
    }
}
