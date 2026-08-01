<?php

namespace Database\Factories;

use App\DeferralType;
use App\Models\Deferral;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Deferral>
 */
class DeferralFactory extends Factory
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
            'created_by' => User::factory()->staff(),
            'type' => DeferralType::Temporary,
            'reason' => 'Temporary medical deferral',
            'notes' => null,
            'starts_at' => today(),
            'ends_at' => today()->addDays(30),
            'is_active' => true,
            'lifted_at' => null,
            'lifted_by' => null,
        ];
    }

    public function permanent(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => DeferralType::Permanent,
            'ends_at' => null,
        ]);
    }
}
