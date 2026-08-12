<?php

namespace Database\Factories;

use App\Models\BloodCenter;
use App\Models\OfflineCollectionDevice;
use App\Models\User;
use App\OfflineCollectionDeviceStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<OfflineCollectionDevice> */
class OfflineCollectionDeviceFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'device_uuid' => (string) Str::uuid(),
            'blood_center_id' => BloodCenter::factory()->state(['offline_collection_enabled' => true]),
            'assigned_to' => User::factory()->staff(),
            'name' => 'Collection tablet '.fake()->unique()->numberBetween(1, 9999),
            'status' => OfflineCollectionDeviceStatus::Active,
            'credential_fingerprint' => hash('sha256', Str::random(64)),
            'last_synced_at' => null,
            'revoked_at' => null,
            'revoked_by' => null,
            'revocation_reason' => null,
        ];
    }
}
