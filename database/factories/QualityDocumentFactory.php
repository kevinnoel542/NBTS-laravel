<?php

namespace Database\Factories;

use App\Models\QualityDocument;
use App\Models\User;
use App\QualityDocumentStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QualityDocument>
 */
class QualityDocumentFactory extends Factory
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
            'document_code' => fake()->unique()->bothify('SOP-####'),
            'version' => 1,
            'title' => 'Component traceability SOP',
            'document_type' => 'sop',
            'status' => QualityDocumentStatus::Effective,
            'applies_to_workflows' => ['components', 'recall'],
            'summary' => 'Construction quality document.',
            'approved_at' => now(),
            'effective_from' => today(),
            'retired_at' => null,
        ];
    }
}
