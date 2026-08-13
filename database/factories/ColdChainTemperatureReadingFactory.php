<?php

namespace Database\Factories;

use App\Models\ColdChainDevice;
use App\Models\ColdChainTemperatureReading;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ColdChainTemperatureReading>
 */
class ColdChainTemperatureReadingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cold_chain_device_id' => ColdChainDevice::factory(),
            'recorded_by' => User::factory()->staff(),
            'temperature_c' => 4.00,
            'recorded_at' => now(),
            'sync_state' => 'manual',
            'payload' => ['source' => 'factory'],
        ];
    }
}
