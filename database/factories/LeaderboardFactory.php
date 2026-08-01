<?php

namespace Database\Factories;

use App\Models\Leaderboard;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Leaderboard>
 */
class LeaderboardFactory extends Factory
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
            'period' => fake()->randomElement(['monthly', 'yearly', 'all_time']),
            'donation_count' => fake()->numberBetween(0, 20),
            'rank' => fake()->numberBetween(1, 100),
        ];
    }
}
