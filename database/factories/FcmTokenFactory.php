<?php

namespace Database\Factories;

use App\DevicePlatform;
use App\Models\FcmToken;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FcmToken>
 */
class FcmTokenFactory extends Factory
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
            'token' => fake()->unique()->regexify('[A-Za-z0-9_-]{160}'),
            'device_type' => DevicePlatform::Android,
        ];
    }
}
