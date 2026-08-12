<?php

namespace Database\Factories;

use App\Models\OrganizationUnit;
use App\Models\StaffCompetency;
use App\Models\User;
use App\StaffCompetencyStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StaffCompetency>
 */
class StaffCompetencyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->staff(),
            'organization_unit_id' => OrganizationUnit::factory(),
            'code' => fake()->unique()->bothify('COMP-###-??'),
            'name' => fake()->randomElement([
                'Donor reception',
                'Donor screening',
                'Whole-blood collection',
                'Inventory handling',
            ]),
            'status' => StaffCompetencyStatus::Active,
            'valid_from' => today()->subYear(),
            'expires_at' => today()->addYear(),
            'verified_by' => null,
            'notes' => null,
        ];
    }

    public function expired(): static
    {
        return $this->state(fn (): array => [
            'expires_at' => today()->subDay(),
            'status' => StaffCompetencyStatus::Expired,
        ]);
    }
}
