<?php

namespace Database\Factories;

use App\Models\BloodCenter;
use App\Models\RecallCase;
use App\Models\User;
use App\QualitySeverity;
use App\RecallCaseStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecallCase>
 */
class RecallCaseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'blood_center_id' => BloodCenter::factory(),
            'opened_by' => User::factory()->staff(),
            'decision_authority_id' => null,
            'closed_by' => null,
            'case_reference' => fake()->unique()->bothify('RC-########'),
            'trigger_type' => 'reactive_changed_result',
            'severity' => QualitySeverity::Critical,
            'status' => RecallCaseStatus::Open,
            'description' => 'Construction recall case for affected component traceability.',
            'trigger_evidence' => ['source' => 'laboratory'],
            'containment_actions' => [],
            'notification_plan' => [],
            'regulator_communication' => [],
            'opened_at' => now(),
            'trace_started_at' => null,
            'deadline_at' => now()->addDay(),
            'closed_at' => null,
            'closure_summary' => null,
            'unresolved_exception_reason' => null,
            'approved_for_closure_at' => null,
        ];
    }
}
