<?php

namespace Database\Factories;

use App\Models\IntegrationEndpoint;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IntegrationEndpoint>
 */
class IntegrationEndpointFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'owner_id' => User::factory()->staff(),
            'system_code' => fake()->unique()->bothify('INT-####'),
            'name' => 'Approved HMIS gateway',
            'endpoint_type' => 'hmis',
            'standard_profile' => 'fhir-r4-approved-profile',
            'base_url' => 'https://hmis.example.test/fhir',
            'encrypted_config' => ['token' => fake()->sha256()],
            'acknowledgement_required' => true,
            'idempotency_required' => true,
            'sequence_check_required' => true,
            'dead_letter_enabled' => true,
            'retry_policy' => ['max_attempts' => 5, 'backoff_minutes' => [1, 5, 15]],
            'status' => 'approved',
            'approved_at' => now(),
        ];
    }
}
