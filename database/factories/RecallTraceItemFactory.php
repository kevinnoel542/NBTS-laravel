<?php

namespace Database\Factories;

use App\Models\RecallCase;
use App\Models\RecallTraceItem;
use App\RecallTraceItemStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecallTraceItem>
 */
class RecallTraceItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'recall_case_id' => RecallCase::factory(),
            'donation_id' => null,
            'blood_unit_id' => null,
            'blood_component_id' => null,
            'hospital_id' => null,
            'hospital_blood_request_id' => null,
            'transfusion_record_id' => null,
            'trace_direction' => 'forward',
            'item_type' => 'component',
            'item_identifier' => fake()->unique()->bothify('CMP-########'),
            'current_location' => 'Component store',
            'status' => RecallTraceItemStatus::Located,
            'notifications' => [],
            'disposition' => [],
            'located_at' => now(),
            'notified_at' => null,
            'resolved_at' => null,
            'exception_reason' => null,
        ];
    }
}
