<?php

namespace Database\Factories;

use App\LaboratoryEquipmentStatus;
use App\LaboratoryInterfaceMode;
use App\Models\BloodCenter;
use App\Models\LaboratoryEquipment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LaboratoryEquipment>
 */
class LaboratoryEquipmentFactory extends Factory
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
            'code' => 'EQ-'.fake()->unique()->numerify('######'),
            'name' => fake()->randomElement(['ELISA bench reader', 'Manual grouping bench', 'Centrifuge']),
            'equipment_type' => 'analyzer',
            'interface_mode' => LaboratoryInterfaceMode::Manual,
            'status' => LaboratoryEquipmentStatus::Active,
            'calibration_due_on' => today()->addMonths(3),
            'maintenance_due_on' => today()->addMonths(6),
            'last_validated_at' => now()->subMonth(),
            'downtime_started_at' => null,
        ];
    }
}
