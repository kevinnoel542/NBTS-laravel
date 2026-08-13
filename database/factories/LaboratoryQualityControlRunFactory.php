<?php

namespace Database\Factories;

use App\LaboratoryQualityControlStatus;
use App\Models\LaboratoryEquipment;
use App\Models\LaboratoryQualityControlRun;
use App\Models\LaboratoryReagentLot;
use App\Models\LaboratoryTestCatalog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LaboratoryQualityControlRun>
 */
class LaboratoryQualityControlRunFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'laboratory_test_catalog_id' => LaboratoryTestCatalog::factory(),
            'laboratory_equipment_id' => LaboratoryEquipment::factory(),
            'laboratory_reagent_lot_id' => LaboratoryReagentLot::factory(),
            'performed_by' => User::factory()->staff(),
            'reviewed_by' => null,
            'status' => LaboratoryQualityControlStatus::Passed,
            'control_lot' => fake()->bothify('QC-####'),
            'expected_results' => ['positive_control' => 'positive', 'negative_control' => 'negative'],
            'observed_results' => ['positive_control' => 'positive', 'negative_control' => 'negative'],
            'performed_at' => now()->subHour(),
            'reviewed_at' => null,
            'failure_reason' => null,
        ];
    }
}
