<?php

namespace Database\Factories;

use App\Models\ComponentProductCatalog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ComponentProductCatalog>
 */
class ComponentProductCatalogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->bothify('RCC-###'),
            'name' => 'Red cell concentrate',
            'component_type' => 'red_cells',
            'production_method' => 'centrifugation',
            'additive_solution' => 'SAGM',
            'default_volume_ml' => 280,
            'storage_temperature_min_c' => 2.00,
            'storage_temperature_max_c' => 6.00,
            'shelf_life_days' => 35,
            'special_attributes' => ['leukoreduced' => false],
            'quality_criteria' => ['visual_inspection' => 'passed'],
            'is_active' => true,
            'effective_from' => today(),
            'approved_at' => now(),
            'approved_by' => User::factory()->staff(),
        ];
    }
}
