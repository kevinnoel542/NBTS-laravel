<?php

namespace Database\Factories;

use App\Models\BloodUnit;
use App\Models\ReleaseAuthorization;
use App\Models\User;
use App\ReleaseAuthorizationDecision;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReleaseAuthorization>
 */
class ReleaseAuthorizationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'blood_unit_id' => BloodUnit::factory(),
            'criteria_version' => 'NBTS-P7-REL-AUTH-v1',
            'decision' => ReleaseAuthorizationDecision::RoutineRelease,
            'evaluated_tests' => [],
            'exceptions' => [],
            'approved_by' => User::factory()->staff(),
            'independent_approved_by' => null,
            'released_by' => null,
            'authorized_at' => now(),
            'reason' => 'Release authorization fixture.',
            'electronic_signature' => true,
        ];
    }
}
