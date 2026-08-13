<?php

namespace Database\Factories;

use App\LaboratoryTestCategory;
use App\Models\LaboratoryTestCatalog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LaboratoryTestCatalog>
 */
class LaboratoryTestCatalogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'LAB-'.fake()->unique()->bothify('??-####'),
            'name' => fake()->randomElement(['HIV screening', 'HBsAg screening', 'ABO confirmation']),
            'category' => LaboratoryTestCategory::TtiScreening,
            'specimen_type' => 'serology',
            'method' => 'Manual ELISA',
            'algorithm_version' => 'construction-v1',
            'result_units' => null,
            'reference_range' => null,
            'release_blocking_interpretations' => ['reactive', 'positive', 'discrepant', 'invalid'],
            'is_required_for_release' => true,
            'is_active' => true,
            'effective_from' => today(),
            'approved_at' => now(),
            'approved_by' => User::factory()->staff(),
        ];
    }
}
