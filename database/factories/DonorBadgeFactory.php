<?php

namespace Database\Factories;

use App\Models\Badge;
use App\Models\DonorBadge;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DonorBadge>
 */
class DonorBadgeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->donor(),
            'badge_id' => Badge::factory(),
            'awarded_at' => now()->subDays(fake()->numberBetween(0, 90)),
        ];
    }
}
