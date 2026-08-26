<?php

namespace Database\Factories;

use App\HaemovigilanceEventStatus;
use App\HaemovigilanceEventType;
use App\Models\BloodCenter;
use App\Models\HaemovigilanceEvent;
use App\Models\User;
use App\QualitySeverity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HaemovigilanceEvent>
 */
class HaemovigilanceEventFactory extends Factory
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
            'hospital_id' => null,
            'donor_id' => null,
            'hospital_blood_request_id' => null,
            'transfusion_record_id' => null,
            'blood_component_id' => null,
            'reported_by' => User::factory()->staff(),
            'assigned_to' => null,
            'closed_by' => null,
            'event_reference' => fake()->unique()->bothify('HV-########'),
            'event_type' => HaemovigilanceEventType::DonorReaction,
            'severity' => QualitySeverity::Medium,
            'status' => HaemovigilanceEventStatus::Open,
            'reaction_type' => 'vasovagal',
            'symptoms' => ['dizziness'],
            'occurred_at' => now(),
            'immediate_action' => 'Donation stopped and donor monitored.',
            'treatment' => 'Oral fluids and observation.',
            'referral' => null,
            'outcome' => 'Recovered at center.',
            'equipment_context' => [],
            'investigation_context' => [],
            'classification' => null,
            'imputability' => null,
            'reporting_state' => 'recorded',
            'supply_context' => [],
            'notifications' => [],
            'escalated_at' => null,
            'followup_due_at' => now()->addDays(2),
            'closed_at' => null,
        ];
    }
}
