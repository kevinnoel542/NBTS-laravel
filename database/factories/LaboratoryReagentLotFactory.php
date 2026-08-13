<?php

namespace Database\Factories;

use App\LaboratoryReagentStatus;
use App\LaboratoryReagentValidationState;
use App\Models\LaboratoryReagentLot;
use App\Models\LaboratoryTestCatalog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LaboratoryReagentLot>
 */
class LaboratoryReagentLotFactory extends Factory
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
            'reagent_name' => fake()->randomElement(['ELISA kit', 'Grouping antisera', 'Control reagent']),
            'lot_number' => fake()->unique()->bothify('LOT-####-??'),
            'manufacturer' => fake()->company(),
            'status' => LaboratoryReagentStatus::Usable,
            'validation_state' => LaboratoryReagentValidationState::Validated,
            'storage_location' => 'Laboratory reagent fridge',
            'received_on' => today()->subDays(10),
            'expires_on' => today()->addMonths(6),
            'validated_at' => now()->subDays(9),
            'recalled_at' => null,
        ];
    }
}
