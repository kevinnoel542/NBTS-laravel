<?php

namespace Database\Factories;

use App\Models\SupportIncident;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupportIncident>
 */
class SupportIncidentFactory extends Factory
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
            'blood_center_id' => null,
            'recurrence_link_id' => null,
            'incident_reference' => fake()->unique()->bothify('INC-########'),
            'severity' => 'high',
            'service' => 'integration_gateway',
            'impact' => 'Delayed acknowledgement from approved external system.',
            'status' => 'open',
            'workaround' => 'Manual reconciliation queue monitored by operations.',
            'root_cause' => null,
            'communication_log' => [['at' => now()->toIso8601String(), 'message' => 'Owner notified.']],
            'escalation_targets' => ['ict_security', 'operations_manager'],
            'acknowledged_at' => now(),
            'restored_at' => null,
            'resolved_at' => null,
        ];
    }
}
