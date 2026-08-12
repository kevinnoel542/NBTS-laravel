<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\OrganizationUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Department>
 */
class DepartmentFactory extends Factory
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
            'owner_user_id' => null,
            'code' => fake()->unique()->bothify('DEPT-###-??'),
            'name' => fake()->randomElement([
                'Reception',
                'Screening and counselling',
                'Collection',
                'Laboratory',
                'Inventory and storage',
                'Quality and haemovigilance',
            ]),
            'description' => fake()->sentence(),
            'escalation_contact' => fake()->phoneNumber(),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
