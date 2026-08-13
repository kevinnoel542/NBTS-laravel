<?php

namespace Database\Factories;

use App\ColdChainAlarmStatus;
use App\Models\ColdChainAlarm;
use App\Models\ColdChainDevice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ColdChainAlarm>
 */
class ColdChainAlarmFactory extends Factory
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
            'cold_chain_excursion_id' => null,
            'acknowledged_by' => null,
            'status' => ColdChainAlarmStatus::Open,
            'triggered_at' => now(),
            'acknowledged_at' => null,
            'escalated_at' => null,
            'response_target_at' => now()->addMinutes(30),
            'summary' => 'Temperature outside approved range',
            'threshold_min_c' => 2.00,
            'threshold_max_c' => 6.00,
            'observed_min_c' => null,
            'observed_max_c' => 9.50,
        ];
    }
}
