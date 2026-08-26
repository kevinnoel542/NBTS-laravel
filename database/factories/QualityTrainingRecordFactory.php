<?php

namespace Database\Factories;

use App\Models\QualityTrainingRecord;
use App\Models\User;
use App\QualityTrainingStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QualityTrainingRecord>
 */
class QualityTrainingRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->staff(),
            'quality_document_id' => null,
            'verified_by' => User::factory()->staff(),
            'competency_code' => 'HV-INVESTIGATION',
            'title' => 'Haemovigilance investigation competency',
            'status' => QualityTrainingStatus::Competent,
            'trained_on' => today()->subMonth(),
            'valid_until' => today()->addYear(),
            'reassessment_due_at' => now()->addYear(),
            'retraining_required' => false,
            'evidence_reference' => fake()->bothify('TRN-####'),
            'notes' => null,
        ];
    }
}
