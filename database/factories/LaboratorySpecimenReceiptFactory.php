<?php

namespace Database\Factories;

use App\LaboratorySpecimenReceiptStatus;
use App\Models\CollectionEpisode;
use App\Models\LaboratorySpecimenReceipt;
use App\Models\Specimen;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LaboratorySpecimenReceipt>
 */
class LaboratorySpecimenReceiptFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'specimen_id' => Specimen::factory(),
            'collection_episode_id' => fn (array $attributes): int => Specimen::query()->findOrFail($attributes['specimen_id'])->collection_episode_id,
            'collection_container_id' => fn (array $attributes): ?int => Specimen::query()->findOrFail($attributes['specimen_id'])->collection_container_id,
            'blood_center_id' => fn (array $attributes): int => CollectionEpisode::query()->findOrFail($attributes['collection_episode_id'])->blood_center_id,
            'received_by' => User::factory()->staff(),
            'scanned_identifier' => fn (array $attributes): string => Specimen::query()->findOrFail($attributes['specimen_id'])->specimen_identifier,
            'receiving_station' => 'Laboratory specimen reception',
            'status' => LaboratorySpecimenReceiptStatus::Accepted,
            'received_at' => now(),
            'rejection_reason' => null,
            'exception_notes' => null,
        ];
    }
}
