<?php

namespace Database\Factories;

use App\Models\OrganizationUnit;
use App\OrganizationUnitStatus;
use App\OrganizationUnitType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrganizationUnit>
 */
class OrganizationUnitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'parent_id' => null,
            'code' => fake()->unique()->bothify('UNIT-####-????'),
            'name' => fake()->company().' Operations',
            'short_name' => fake()->companySuffix(),
            'type' => OrganizationUnitType::BloodCenter,
            'status' => OrganizationUnitStatus::Active,
            'effective_from' => today()->subYear(),
            'effective_until' => null,
        ];
    }

    public function national(): static
    {
        return $this->state(fn (): array => [
            'parent_id' => null,
            'type' => OrganizationUnitType::National,
        ]);
    }

    public function hospital(): static
    {
        return $this->state(fn (): array => ['type' => OrganizationUnitType::Hospital]);
    }

    public function suspended(): static
    {
        return $this->state(fn (): array => ['status' => OrganizationUnitStatus::Suspended]);
    }
}
