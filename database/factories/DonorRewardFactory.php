<?php

namespace Database\Factories;

use App\DonorRewardStatus;
use App\Models\DonorReward;
use App\Models\Reward;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DonorReward>
 */
class DonorRewardFactory extends Factory
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
            'reward_id' => Reward::factory(),
            'status' => DonorRewardStatus::Earned,
            'awarded_at' => now()->subDays(fake()->numberBetween(0, 90)),
            'redeemed_at' => null,
        ];
    }

    public function redeemed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => DonorRewardStatus::Redeemed,
            'redeemed_at' => now(),
        ]);
    }
}
