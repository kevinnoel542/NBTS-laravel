<?php

namespace Database\Factories;

use App\Models\ChangeControl;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChangeControl>
 */
class ChangeControlFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'requested_by' => User::factory()->staff(),
            'approved_by' => User::factory()->staff(),
            'change_reference' => fake()->unique()->bothify('CHG-########'),
            'classification' => 'clinical_safety',
            'title' => 'Update compatibility rule configuration',
            'scope' => ['workflow' => 'compatibility'],
            'risk_level' => 'high',
            'approvals' => ['clinical' => true, 'laboratory' => true, 'quality' => true, 'validation' => true],
            'regression_evidence' => ['tests' => ['PhaseNineCompatibilityIssueTest']],
            'migration_plan' => 'Deploy after validation window.',
            'rollback_plan' => 'Restore previous active configuration version.',
            'release_notes' => 'Approved controlled rule update.',
            'training_impact' => 'Brief hospital blood-bank staff.',
            'emergency_change' => false,
            'status' => 'approved',
            'effective_at' => now()->addDay(),
            'retrospective_review_due_at' => null,
            'approved_at' => now(),
        ];
    }
}
