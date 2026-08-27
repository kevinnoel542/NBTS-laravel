<?php

namespace Database\Factories;

use App\Models\KpiDefinition;
use App\Models\ReportSnapshot;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReportSnapshot>
 */
class ReportSnapshotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kpi_definition_id' => KpiDefinition::factory(),
            'generated_by' => User::factory()->staff(),
            'report_reference' => fake()->unique()->bothify('RPT-########'),
            'report_type' => 'national_kpi_summary',
            'source_period_start' => today()->startOfMonth(),
            'source_period_end' => today()->endOfMonth(),
            'scope' => ['level' => 'national'],
            'metrics' => [
                'collection_total' => 100,
                'safety_total' => 100,
                'utilization_total' => 85,
                'wastage_total' => 5,
                'adverse_event_total' => 1,
            ],
            'reconciliation' => ['source_total' => 100, 'reported_total' => 100],
            'deidentified' => true,
            'national_dashboard_ready' => true,
            'status' => 'generated',
            'generated_at' => now(),
        ];
    }
}
