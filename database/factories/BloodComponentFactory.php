<?php

namespace Database\Factories;

use App\BloodGroup;
use App\ComponentStatus;
use App\Models\BloodComponent;
use App\Models\BloodUnit;
use App\Models\ComponentProcessingEvent;
use App\Models\ComponentProductCatalog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BloodComponent>
 */
class BloodComponentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_identifier' => fake()->unique()->bothify('CMP-########'),
            'blood_unit_id' => BloodUnit::factory(),
            'donation_id' => fn (array $attributes): int => BloodUnit::query()->findOrFail($attributes['blood_unit_id'])->donation_id,
            'parent_component_id' => null,
            'component_product_catalog_id' => ComponentProductCatalog::factory(),
            'component_processing_event_id' => fn (array $attributes): int => ComponentProcessingEvent::factory()->create([
                'blood_unit_id' => $attributes['blood_unit_id'],
            ])->id,
            'blood_center_id' => fn (array $attributes): int => BloodUnit::query()->findOrFail($attributes['blood_unit_id'])->blood_center_id,
            'blood_group' => BloodGroup::OPositive,
            'status' => ComponentStatus::Available,
            'storage_location' => 'Main refrigerator A',
            'cold_chain_device_id' => null,
            'special_attributes' => [],
            'expiry_date' => today()->addDays(35),
            'processed_at' => now(),
            'released_at' => now(),
        ];
    }
}
