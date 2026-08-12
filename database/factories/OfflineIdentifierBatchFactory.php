<?php

namespace Database\Factories;

use App\Models\OfflineCollectionDevice;
use App\Models\OfflineIdentifierBatch;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<OfflineIdentifierBatch> */
class OfflineIdentifierBatchFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'offline_collection_device_id' => OfflineCollectionDevice::factory(),
            'blood_center_id' => function (array $attributes): int {
                $deviceId = (int) $attributes['offline_collection_device_id'];

                return OfflineCollectionDevice::query()->findOrFail($deviceId)->blood_center_id;
            },
            'year' => (int) now()->format('Y'),
            'prefix' => 'TZNBTSTST',
            'start_sequence' => fake()->unique()->numberBetween(1, 900000),
            'end_sequence' => fn (array $attributes): int => ((int) $attributes['start_sequence']) + 49,
            'next_sequence' => fn (array $attributes): int => (int) $attributes['start_sequence'],
            'issued_by' => null,
            'issued_at' => now(),
            'expires_at' => now()->addDays(7),
            'revoked_at' => null,
        ];
    }
}
