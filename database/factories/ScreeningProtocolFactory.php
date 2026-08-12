<?php

namespace Database\Factories;

use App\Models\ScreeningProtocol;
use App\Models\User;
use App\ScreeningProtocolStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ScreeningProtocol> */
class ScreeningProtocolFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'code' => 'NBTS-WB',
            'version' => fake()->unique()->numberBetween(1, 5000),
            'title' => 'Whole-blood donor screening',
            'status' => ScreeningProtocolStatus::Active,
            'questionnaire' => [
                ['code' => 'consent_confirmed', 'required' => true, 'type' => 'boolean'],
                ['code' => 'feels_well', 'required' => true, 'type' => 'boolean'],
            ],
            'rules' => [
                'minimum_age' => 18,
                'maximum_age' => 65,
                'minimum_weight_kg' => 50,
            ],
            'effective_from' => today(),
            'effective_until' => null,
            'is_construction_only' => false,
            'approved_by' => User::factory()->nbtsAdmin(),
            'approved_at' => now(),
        ];
    }
}
