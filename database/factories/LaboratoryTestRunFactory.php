<?php

namespace Database\Factories;

use App\LaboratoryTestRunStatus;
use App\Models\LaboratoryEquipment;
use App\Models\LaboratoryReagentLot;
use App\Models\LaboratoryTestOrder;
use App\Models\LaboratoryTestRun;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LaboratoryTestRun>
 */
class LaboratoryTestRunFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'laboratory_test_order_id' => LaboratoryTestOrder::factory(),
            'laboratory_test_catalog_id' => fn (array $attributes): int => LaboratoryTestOrder::query()->findOrFail($attributes['laboratory_test_order_id'])->laboratory_test_catalog_id,
            'laboratory_equipment_id' => LaboratoryEquipment::factory(),
            'laboratory_reagent_lot_id' => LaboratoryReagentLot::factory(),
            'operator_id' => User::factory()->staff(),
            'method_version' => 'construction-v1',
            'status' => LaboratoryTestRunStatus::Completed,
            'started_at' => now()->subMinutes(30),
            'ended_at' => now(),
            'control_lot' => fake()->bothify('CTRL-####'),
            'raw_payload' => ['source' => 'manual_entry'],
            'comments' => null,
        ];
    }
}
