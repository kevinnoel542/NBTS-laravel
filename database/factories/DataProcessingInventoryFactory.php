<?php

namespace Database\Factories;

use App\Models\DataProcessingInventory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DataProcessingInventory>
 */
class DataProcessingInventoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'owner_id' => User::factory()->staff(),
            'approved_by' => User::factory()->staff(),
            'process_code' => fake()->unique()->bothify('DPI-####'),
            'name' => 'Donor registration and eligibility processing',
            'data_subjects' => ['donor'],
            'data_categories' => ['identity', 'contact', 'eligibility', 'donation_history'],
            'purposes' => ['safe_blood_supply', 'donor_care'],
            'lawful_basis' => 'public_health_task',
            'controller' => 'NBTS',
            'processors' => ['approved_hmis_provider'],
            'minimization_controls' => ['patient_reference_hashing', 'role_based_access'],
            'vendor_controls' => ['data_processing_agreement', 'security_due_diligence'],
            'dpia_required' => true,
            'dpia_reference' => fake()->bothify('DPIA-####'),
            'breach_response_playbook' => 'Notify DPO and activate breach triage within approved timeline.',
            'rights_handling' => ['access_request_sla_days' => 30],
            'status' => 'approved',
            'approved_at' => now(),
            'review_due_at' => now()->addYear(),
        ];
    }
}
