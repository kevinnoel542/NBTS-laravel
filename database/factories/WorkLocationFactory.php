<?php

namespace Database\Factories;

use App\Models\OrganizationUnit;
use App\Models\WorkLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkLocation>
 */
class WorkLocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_unit_id' => OrganizationUnit::factory(),
            'department_id' => null,
            'code' => fake()->unique()->bothify('LOC-###-??'),
            'name' => fake()->randomElement([
                'Reception desk',
                'Screening room',
                'Collection area',
                'Testing laboratory',
                'Released inventory store',
                'Dispatch area',
            ]),
            'type' => fake()->randomElement(['work_area', 'storage', 'dispatch']),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
