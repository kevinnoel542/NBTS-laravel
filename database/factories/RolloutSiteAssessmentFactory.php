<?php

namespace Database\Factories;

use App\Models\BloodCenter;
use App\Models\RolloutSiteAssessment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RolloutSiteAssessment>
 */
class RolloutSiteAssessmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'approved_at' => null,
            'approved_by' => null,
            'assessed_at' => now(),
            'assessed_by' => User::factory(),
            'assessment_reference' => 'RSA-'.$this->faker->unique()->numerify('######'),
            'baseline_kpis' => [
                'adoption' => 0,
                'downtime' => 0,
                'expiry' => 0,
                'incident' => 0,
                'request_fill' => 0,
                'safety' => 0,
                'support' => 0,
                'turnaround' => 0,
            ],
            'blood_center_id' => BloodCenter::factory(),
            'data_dictionary_scope' => ['donor', 'collection', 'laboratory', 'component', 'hospital'],
            'inventory_snapshot' => [
                'analyzers' => [],
                'budgets' => [],
                'centers' => [],
                'connectivity' => [],
                'contracts' => [],
                'equipment' => [],
                'forms' => [],
                'hospitals' => [],
                'identifiers' => [],
                'integrations' => [],
                'laws' => [],
                'power' => [],
                'reagents' => [],
                'routes' => [],
                'sensors' => [],
                'sops' => [],
                'staff' => [],
                'storage' => [],
                'volumes' => [],
            ],
            'legal_and_policy_inputs' => ['data_protection', 'clinical_policy', 'retention'],
            'master_data_owners' => ['centers' => 'operations', 'users' => 'ict'],
            'operational_readiness' => ['training' => false, 'hardware' => false, 'support' => false],
            'pilot_scope' => ['centers' => [], 'hospitals' => [], 'workflows' => []],
            'prioritized_backlog' => ['critical' => [], 'must' => [], 'should' => []],
            'risks' => [['level' => 'medium', 'control' => 'pilot review']],
            'safety_case_reference' => 'SAFE-'.$this->faker->unique()->numerify('###'),
            'site_name' => $this->faker->company(),
            'site_type' => 'blood_center',
            'status' => 'draft',
            'target_process_reference' => 'PROC-'.$this->faker->unique()->numerify('###'),
            'workflow_map' => [
                'adverse_event',
                'collection',
                'components',
                'downtime',
                'governance',
                'hospital',
                'laboratory',
                'logistics',
                'recall',
                'storage',
                'transfusion',
            ],
        ];
    }
}
