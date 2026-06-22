<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Badge;
use App\Models\BloodCenter;
use App\Models\BloodInventory;
use App\Models\BloodUnit;
use App\Models\Campaign;
use App\Models\CenterStaff;
use App\Models\Donation;
use App\Models\DonorBadge;
use App\Models\DonorProfile;
use App\Models\DonorReward;
use App\Models\EligibilityRecord;
use App\Models\InventoryAdjustment;
use App\Models\Leaderboard;
use App\Models\LowStockAlert;
use App\Models\Reward;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    private const DEMO_PASSWORD = 'Password123!';

    public function run(): void
    {
        $centers = BloodCenter::query()->orderBy('id')->get();

        if ($centers->isEmpty()) {
            return;
        }

        $admin = $this->user([
            'name' => 'NBTS System Admin',
            'email' => 'admin@nbts.test',
            'phone' => '+255700000001',
            'role' => 'admin',
            'gender' => 'other',
            'region' => 'Dar es Salaam',
            'address' => 'NBTS Headquarters, Dar es Salaam',
        ], 'super_admin');

        $manager = $this->user([
            'name' => 'Asha Mrema',
            'email' => 'manager@nbts.test',
            'phone' => '+255700000002',
            'role' => 'staff',
            'gender' => 'female',
            'region' => 'Dar es Salaam',
            'address' => 'Upanga, Dar es Salaam',
        ], 'center_manager');

        $staff = $this->user([
            'name' => 'Joseph Kileo',
            'email' => 'staff@nbts.test',
            'phone' => '+255700000003',
            'role' => 'staff',
            'gender' => 'male',
            'region' => 'Dar es Salaam',
            'address' => 'Ilala, Dar es Salaam',
        ], 'center_staff');

        CenterStaff::updateOrCreate(
            ['user_id' => $manager->id, 'blood_center_id' => $centers[0]->id],
            ['position' => 'center_manager', 'is_active' => true],
        );
        CenterStaff::updateOrCreate(
            ['user_id' => $staff->id, 'blood_center_id' => $centers[min(1, $centers->count() - 1)]->id],
            ['position' => 'center_staff', 'is_active' => true],
        );

        $donors = collect([
            $this->donor([
                'name' => 'Neema John',
                'email' => 'donor@nbts.test',
                'phone' => '+255700000101',
                'blood_group' => 'O+',
                'gender' => 'female',
                'date_of_birth' => '1995-04-17',
                'region' => 'Dar es Salaam',
                'address' => 'Sinza, Dar es Salaam',
                'last_donation' => now()->subDays(95)->toDateString(),
            ], 'DNR-2026-000101', 3, $staff),
            $this->donor([
                'name' => 'Baraka Ally',
                'email' => 'baraka.ally@example.com',
                'phone' => '+255700000102',
                'blood_group' => 'A+',
                'gender' => 'male',
                'date_of_birth' => '1990-09-03',
                'region' => 'Dodoma',
                'address' => 'Makole, Dodoma',
                'last_donation' => now()->subDays(130)->toDateString(),
            ], 'DNR-2026-000102', 2, $staff),
            $this->donor([
                'name' => 'Rehema Said',
                'email' => 'rehema.said@example.com',
                'phone' => '+255700000103',
                'blood_group' => 'B-',
                'gender' => 'female',
                'date_of_birth' => '1988-12-22',
                'region' => 'Mwanza',
                'address' => 'Ilemela, Mwanza',
                'last_donation' => now()->subDays(65)->toDateString(),
            ], 'DNR-2026-000103', 1, $staff, 'not_yet_eligible'),
        ]);

        $this->campaigns($centers);
        $appointments = $this->appointments($donors, $centers, $manager);
        $this->donations($donors, $centers, $staff, $appointments);
        $this->inventory($centers, $staff);
        $this->loyalty($donors);
    }

    private function user(array $data, string $role): User
    {
        $user = User::updateOrCreate(
            ['email' => $data['email']],
            $data + [
                'password' => self::DEMO_PASSWORD,
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );

        $user->syncRoles([$role]);

        return $user;
    }

    private function donor(array $data, string $donorId, int $totalDonations, User $verifiedBy, string $eligibilityStatus = 'eligible'): User
    {
        $user = $this->user($data + ['role' => 'donor'], 'donor');

        DonorProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'donor_id' => $donorId,
                'blood_group_status' => 'staff_verified',
                'blood_group_verified' => true,
                'blood_group_verified_at' => now()->subDays(30),
                'blood_group_verified_by' => $verifiedBy->id,
                'next_eligible_donation_date' => $eligibilityStatus === 'eligible' ? now()->subDays(1)->toDateString() : now()->addDays(25)->toDateString(),
                'eligibility_status' => $eligibilityStatus,
                'last_eligibility_checked_at' => now()->subDays(7),
                'eligibility_notes' => $eligibilityStatus === 'eligible' ? 'Cleared during routine screening.' : 'Recently donated; wait until the next eligible date.',
                'total_donations' => $totalDonations,
            ],
        );

        EligibilityRecord::updateOrCreate(
            ['user_id' => $user->id, 'created_at' => now()->subDays(7)->startOfDay()],
            [
                'checked_by' => $verifiedBy->id,
                'status' => $eligibilityStatus,
                'age' => now()->parse($data['date_of_birth'])->age,
                'weight_kg' => 68,
                'answers' => ['feeling_well' => true, 'recent_illness' => false],
                'next_eligible_donation_date' => $eligibilityStatus === 'eligible' ? null : now()->addDays(25)->toDateString(),
                'notes' => $eligibilityStatus === 'eligible' ? 'Eligible for whole blood donation.' : 'Temporary waiting period after recent donation.',
            ],
        );

        return $user;
    }

    private function campaigns($centers): void
    {
        $campaigns = [
            [
                'title' => 'Dar es Salaam University Blood Drive',
                'description' => 'Community collection drive targeting university students and nearby residents.',
                'start_date' => now()->addDays(5)->setTime(9, 0),
                'end_date' => now()->addDays(5)->setTime(16, 0),
                'blood_center_id' => $centers[0]->id,
                'location' => 'University of Dar es Salaam, Mlimani Campus',
                'status' => 'upcoming',
                'campaign_type' => 'standard',
            ],
            [
                'title' => 'Dodoma O Negative Emergency Appeal',
                'description' => 'Urgent collection campaign for low O- inventory in the central zone.',
                'start_date' => now()->addDays(2)->setTime(8, 30),
                'end_date' => now()->addDays(2)->setTime(15, 30),
                'blood_center_id' => $centers[min(2, $centers->count() - 1)]->id,
                'location' => 'Nyerere Square, Dodoma',
                'status' => 'upcoming',
                'campaign_type' => 'emergency',
                'target_blood_group' => 'O-',
            ],
            [
                'title' => 'Mwanza Community Donor Day',
                'description' => 'Monthly donor day serving hospitals around Lake Zone.',
                'start_date' => now()->subDays(10)->setTime(9, 0),
                'end_date' => now()->subDays(10)->setTime(16, 0),
                'blood_center_id' => $centers[min(3, $centers->count() - 1)]->id,
                'location' => 'Bugando Medical Centre',
                'status' => 'completed',
                'campaign_type' => 'standard',
            ],
        ];

        foreach ($campaigns as $campaign) {
            Campaign::updateOrCreate(['title' => $campaign['title']], $campaign);
        }
    }

    private function appointments($donors, $centers, User $handler)
    {
        return $donors->map(function (User $donor, int $index) use ($centers, $handler) {
            $scheduledAt = now()->addDays($index + 1)->setTime(9 + $index, 30);

            return Appointment::updateOrCreate(
                ['user_id' => $donor->id, 'scheduled_at' => $scheduledAt],
                [
                    'blood_center_id' => $centers[$index % $centers->count()]->id,
                    'status' => $index === 2 ? 'pending' : 'confirmed',
                    'confirmed_at' => $index === 2 ? null : now()->subDay(),
                    'handled_by' => $index === 2 ? null : $handler->id,
                    'notes' => $index === 0 ? 'Prefers morning appointment.' : 'Routine donation appointment.',
                ],
            );
        });
    }

    private function donations($donors, $centers, User $staff, $appointments): void
    {
        foreach ($donors as $index => $donor) {
            $date = now()->subDays(95 + ($index * 18))->toDateString();
            $center = $centers[$index % $centers->count()];

            $donation = Donation::updateOrCreate(
                ['user_id' => $donor->id, 'donation_date' => $date, 'blood_center_id' => $center->id],
                [
                    'recorded_by' => $staff->id,
                    'appointment_id' => $appointments[$index]?->id,
                    'donation_type' => 'appointment',
                    'blood_group' => $donor->blood_group,
                    'blood_group_verified' => true,
                    'volume_ml' => 450,
                    'status' => 'completed',
                    'notes' => 'Demo completed donation record.',
                ],
            );

            BloodUnit::updateOrCreate(
                ['unit_number' => 'TZ-NBTS-' . now()->format('Y') . '-' . str_pad((string) ($index + 1), 5, '0', STR_PAD_LEFT)],
                [
                    'donation_id' => $donation->id,
                    'donor_id' => $donor->id,
                    'blood_center_id' => $center->id,
                    'blood_group' => $donor->blood_group,
                    'collection_date' => $date,
                    'expiry_date' => now()->parse($date)->addDays(35)->toDateString(),
                    'status' => $index === 2 ? 'testing' : 'available',
                    'current_location' => $center->name,
                    'handled_by' => $staff->id,
                ],
            );
        }
    }

    private function inventory($centers, User $staff): void
    {
        $groups = ['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'];

        foreach ($centers as $centerIndex => $center) {
            foreach ($groups as $groupIndex => $group) {
                $available = max(1, 12 - $centerIndex - $groupIndex);
                $minimum = in_array($group, ['O-', 'B-'], true) ? 6 : 5;

                BloodInventory::updateOrCreate(
                    ['blood_center_id' => $center->id, 'blood_group' => $group],
                    [
                        'available_units' => $available,
                        'reserved_units' => $groupIndex % 3,
                        'minimum_threshold' => $minimum,
                    ],
                );
            }

            InventoryAdjustment::firstOrCreate(
                [
                    'blood_center_id' => $center->id,
                    'blood_group' => 'O+',
                    'reason' => 'opening_demo_stock',
                ],
                [
                    'adjusted_by' => $staff->id,
                    'quantity_delta' => 10,
                    'notes' => 'Opening demo stock balance.',
                ],
            );
        }

        $alertCenter = $centers[min(2, $centers->count() - 1)];
        LowStockAlert::updateOrCreate(
            ['blood_center_id' => $alertCenter->id, 'blood_group' => 'O-', 'status' => 'open'],
            [
                'available_units' => 2,
                'minimum_threshold' => 6,
                'resolved_at' => null,
            ],
        );
    }

    private function loyalty($donors): void
    {
        $badges = Badge::query()->orderBy('donation_threshold')->get();
        $rewards = Reward::query()->orderBy('donation_threshold')->get();

        $donors->each(function (User $donor, int $index) use ($badges, $rewards) {
            $count = $donor->donorProfile?->total_donations ?? 0;

            $badges->where('donation_threshold', '<=', $count)->each(function (Badge $badge) use ($donor) {
                DonorBadge::updateOrCreate(
                    ['user_id' => $donor->id, 'badge_id' => $badge->id],
                    ['awarded_at' => now()->subDays(30)],
                );
            });

            $rewards->where('donation_threshold', '<=', $count)->each(function (Reward $reward) use ($donor) {
                DonorReward::updateOrCreate(
                    ['user_id' => $donor->id, 'reward_id' => $reward->id],
                    ['status' => 'earned', 'awarded_at' => now()->subDays(20)],
                );
            });

            Leaderboard::updateOrCreate(
                ['user_id' => $donor->id, 'period' => 'all_time'],
                ['donation_count' => $count, 'rank' => $index + 1],
            );
        });
    }
}
