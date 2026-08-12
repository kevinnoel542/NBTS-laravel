<?php

namespace Database\Factories;

use App\CollectionLabelStatus;
use App\Models\CollectionContainer;
use App\Models\CollectionLabel;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CollectionLabel> */
class CollectionLabelFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'collection_episode_id' => function (array $attributes): int {
                $containerId = (int) $attributes['collection_container_id'];

                return CollectionContainer::query()->findOrFail($containerId)->collection_episode_id;
            },
            'collection_container_id' => CollectionContainer::factory(),
            'specimen_id' => null,
            'label_identifier' => 'LBL-'.fake()->unique()->numerify('############'),
            'symbology' => 'code_128_b',
            'template_version' => 'NBTS-CONSTRUCTION-1',
            'status' => CollectionLabelStatus::Generated,
            'print_count' => 0,
            'printer_name' => null,
            'printed_by' => null,
            'printed_at' => null,
            'applied_by' => null,
            'applied_at' => null,
            'voided_by' => null,
            'voided_at' => null,
            'reason' => null,
        ];
    }
}
