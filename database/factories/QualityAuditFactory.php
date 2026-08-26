<?php

namespace Database\Factories;

use App\Models\QualityAudit;
use App\Models\User;
use App\QualityAuditStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QualityAudit>
 */
class QualityAuditFactory extends Factory
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
            'lead_auditor_id' => User::factory()->staff(),
            'closed_by' => null,
            'audit_reference' => fake()->unique()->bothify('AUD-########'),
            'audit_type' => 'internal',
            'status' => QualityAuditStatus::Planned,
            'scope' => ['traceability'],
            'findings' => [],
            'linked_deviation_ids' => [],
            'scheduled_on' => today()->addWeek(),
            'started_at' => null,
            'closed_at' => null,
            'accreditation_readiness' => null,
        ];
    }
}
