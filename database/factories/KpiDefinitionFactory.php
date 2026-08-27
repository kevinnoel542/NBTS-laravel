<?php

namespace Database\Factories;

use App\Models\KpiDefinition;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KpiDefinition>
 */
class KpiDefinitionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'approved_by' => User::factory()->staff(),
            'kpi_code' => fake()->unique()->bothify('KPI-###'),
            'name' => 'Donor conversion rate',
            'category' => 'donor',
            'numerator' => 'Completed donations from eligible appointments',
            'denominator' => 'Eligible donor appointments checked in',
            'exclusions' => ['cancelled_before_check_in'],
            'source_models' => ['appointments', 'donations', 'eligibility_records'],
            'owner' => 'National operations',
            'frequency' => 'monthly',
            'target' => '>= 65%',
            'data_quality_checks' => ['center_scope_present', 'period_closed', 'duplicate_donor_reviewed'],
            'anti_gaming_controls' => ['balance_with_deferrals', 'balance_with_reactions', 'balance_with_wastage'],
            'status' => 'approved',
            'effective_from' => today(),
            'approved_at' => now(),
        ];
    }
}
