<?php

namespace Database\Factories;

use App\LaboratoryTestOrderStatus;
use App\Models\LaboratorySpecimenReceipt;
use App\Models\LaboratoryTestCatalog;
use App\Models\LaboratoryTestOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LaboratoryTestOrder>
 */
class LaboratoryTestOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'laboratory_specimen_receipt_id' => LaboratorySpecimenReceipt::factory(),
            'specimen_id' => fn (array $attributes): int => LaboratorySpecimenReceipt::query()->findOrFail($attributes['laboratory_specimen_receipt_id'])->specimen_id,
            'laboratory_test_catalog_id' => LaboratoryTestCatalog::factory(),
            'ordered_by' => User::factory()->staff(),
            'status' => LaboratoryTestOrderStatus::Ordered,
            'ordered_at' => now(),
            'due_at' => now()->addDay(),
            'cancellation_reason' => null,
        ];
    }
}
