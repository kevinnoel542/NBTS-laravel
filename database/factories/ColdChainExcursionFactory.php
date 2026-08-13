<?php

namespace Database\Factories;

use App\ColdChainExcursionStatus;
use App\Models\ColdChainDevice;
use App\Models\ColdChainExcursion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ColdChainExcursion>
 */
class ColdChainExcursionFactory extends Factory
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
            'opened_by' => User::factory()->staff(),
            'closed_by' => null,
            'status' => ColdChainExcursionStatus::Open,
            'started_at' => now(),
            'ended_at' => null,
            'observed_min_c' => null,
            'observed_max_c' => 9.50,
            'affected_component_ids' => [],
            'disposition' => null,
            'capa' => null,
            'opened_at' => now(),
            'closed_at' => null,
        ];
    }
}
