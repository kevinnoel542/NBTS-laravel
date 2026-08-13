<?php

namespace Database\Factories;

use App\LaboratoryQualityEventStatus;
use App\LaboratoryQualityEventType;
use App\LaboratoryQualitySeverity;
use App\Models\BloodCenter;
use App\Models\LaboratoryQualityEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LaboratoryQualityEvent>
 */
class LaboratoryQualityEventFactory extends Factory
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
            'laboratory_test_catalog_id' => null,
            'laboratory_equipment_id' => null,
            'laboratory_reagent_lot_id' => null,
            'opened_by' => User::factory()->staff(),
            'closed_by' => null,
            'type' => LaboratoryQualityEventType::Deviation,
            'severity' => LaboratoryQualitySeverity::Medium,
            'status' => LaboratoryQualityEventStatus::Open,
            'title' => 'Laboratory deviation under review',
            'description' => 'Construction QA event for laboratory workflow verification.',
            'affected_identifiers' => [],
            'corrective_action' => null,
            'opened_at' => now(),
            'closed_at' => null,
        ];
    }
}
