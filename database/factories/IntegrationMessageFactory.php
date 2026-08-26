<?php

namespace Database\Factories;

use App\Models\IntegrationEndpoint;
use App\Models\IntegrationMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IntegrationMessage>
 */
class IntegrationMessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'integration_endpoint_id' => IntegrationEndpoint::factory(),
            'message_reference' => fake()->unique()->bothify('MSG-########'),
            'idempotency_key' => fake()->unique()->uuid(),
            'sequence_number' => fake()->numberBetween(1, 1000),
            'direction' => 'inbound',
            'message_type' => 'Observation',
            'status' => 'acknowledged',
            'payload_digest' => fake()->sha256(),
            'attempts' => 1,
            'acknowledgement_payload' => ['accepted' => true],
            'last_error' => null,
            'next_retry_at' => null,
            'acknowledged_at' => now(),
            'dead_lettered_at' => null,
            'reconciled_at' => now(),
        ];
    }
}
