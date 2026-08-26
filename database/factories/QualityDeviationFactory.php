<?php

namespace Database\Factories;

use App\Models\QualityDeviation;
use App\Models\User;
use App\QualityDeviationStatus;
use App\QualitySeverity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QualityDeviation>
 */
class QualityDeviationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'blood_center_id' => null,
            'hospital_id' => null,
            'opened_by' => User::factory()->staff(),
            'owner_id' => User::factory()->staff(),
            'quality_approved_by' => null,
            'closed_by' => null,
            'deviation_reference' => fake()->unique()->bothify('DEV-########'),
            'type' => 'nonconformity',
            'severity' => QualitySeverity::High,
            'status' => QualityDeviationStatus::Open,
            'title' => 'Construction deviation',
            'description' => 'Deviation recorded for automated workflow verification.',
            'affected_records' => [],
            'containment' => null,
            'root_cause' => null,
            'correction' => null,
            'corrective_action' => null,
            'preventive_action' => null,
            'due_at' => now()->addWeek(),
            'effectiveness_check' => null,
            'effectiveness_checked_at' => null,
            'closure_evidence' => null,
            'opened_at' => now(),
            'closed_at' => null,
        ];
    }
}
