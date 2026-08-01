<?php

namespace Database\Factories;

use App\Models\BloodCenter;
use App\Models\CenterStaff;
use App\Models\User;
use App\RoleName;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CenterStaff>
 */
class CenterStaffFactory extends Factory
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
            'blood_center_id' => BloodCenter::factory(),
            'position' => RoleName::CenterStaff->value,
            'is_active' => true,
        ];
    }

    public function manager(): static
    {
        return $this->state(fn (array $attributes): array => [
            'position' => RoleName::CenterManager->value,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }
}
