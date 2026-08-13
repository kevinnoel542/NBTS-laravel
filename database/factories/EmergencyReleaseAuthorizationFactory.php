<?php

namespace Database\Factories;

use App\EmergencyReleaseStatus;
use App\Models\BloodComponent;
use App\Models\EmergencyReleaseAuthorization;
use App\Models\HospitalBloodRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmergencyReleaseAuthorization>
 */
class EmergencyReleaseAuthorizationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'hospital_blood_request_id' => HospitalBloodRequest::factory(),
            'blood_component_id' => BloodComponent::factory(),
            'authorized_by' => User::factory()->staff(),
            'acknowledged_by' => User::factory()->staff(),
            'clinical_authorizer_name' => fake()->name(),
            'clinical_authorizer_contact' => fake()->phoneNumber(),
            'reason' => 'Life-threatening bleeding emergency',
            'risk_acknowledgement' => 'Clinical team acknowledges uncrossmatched emergency release risk.',
            'status' => EmergencyReleaseStatus::Acknowledged,
            'authorized_at' => now(),
            'acknowledged_at' => now(),
            'retrospective_completion_due_at' => now()->addDay(),
            'retrospective_completed_at' => null,
        ];
    }
}
