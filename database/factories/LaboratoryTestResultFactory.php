<?php

namespace Database\Factories;

use App\LaboratoryTestInterpretation;
use App\LaboratoryTestResultStatus;
use App\Models\LaboratoryQualityControlRun;
use App\Models\LaboratoryTestOrder;
use App\Models\LaboratoryTestResult;
use App\Models\LaboratoryTestRun;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LaboratoryTestResult>
 */
class LaboratoryTestResultFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'laboratory_test_order_id' => LaboratoryTestOrder::factory(),
            'laboratory_test_run_id' => LaboratoryTestRun::factory(),
            'laboratory_test_catalog_id' => fn (array $attributes): int => LaboratoryTestOrder::query()->findOrFail($attributes['laboratory_test_order_id'])->laboratory_test_catalog_id,
            'laboratory_quality_control_run_id' => LaboratoryQualityControlRun::factory(),
            'entered_by' => User::factory()->staff(),
            'verified_by' => null,
            'result_value' => 'non-reactive',
            'interpretation' => LaboratoryTestInterpretation::NonReactive,
            'status' => LaboratoryTestResultStatus::Preliminary,
            'is_release_blocking' => false,
            'resulted_at' => now(),
            'verified_at' => null,
            'comments' => null,
        ];
    }
}
