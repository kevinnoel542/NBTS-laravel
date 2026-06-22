<?php

namespace App\Services;

use App\Models\Donation;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DonationRecordingService
{
    public function __construct(
        private readonly EligibilityService $eligibilityService,
        private readonly LoyaltyService $loyaltyService,
        private readonly InventoryService $inventoryService,
    ) {
    }

    public function record(array $data, ?User $recordedBy = null): Donation
    {
        return DB::transaction(function () use ($data, $recordedBy): Donation {
            $donor = User::findOrFail($data['user_id']);

            if (($data['status'] ?? 'completed') === 'completed') {
                $this->eligibilityService->assertEligible($donor);
            }

            $donation = Donation::create(array_merge($data, [
                'recorded_by' => $recordedBy?->id,
                'status' => $data['status'] ?? 'completed',
            ]));

            if ($donation->appointment_id) {
                $donation->appointment()->update([
                    'status' => 'completed',
                    'handled_by' => $recordedBy?->id,
                ]);
            }

            if ($donation->status === 'completed') {
                $this->updateDonorAfterDonation($donation, $recordedBy);
                $this->loyaltyService->awardForDonor($donation->user()->with('donorProfile')->first());
                $this->inventoryService->createUnitFromDonation($donation->load('bloodCenter'), $recordedBy);
            }

            return $donation->load(['user.donorProfile', 'bloodCenter', 'appointment', 'bloodUnit']);
        });
    }

    private function updateDonorAfterDonation(Donation $donation, ?User $recordedBy): void
    {
        $donationDate = Carbon::parse($donation->donation_date);
        $nextEligibleDate = $donationDate->copy()->addDays(90)->toDateString();

        $donation->user()->update([
            'blood_group' => $donation->blood_group,
            'last_donation' => $donationDate->toDateString(),
        ]);

        $donation->user->donorProfile()->updateOrCreate(
            ['user_id' => $donation->user_id],
            [
                'donor_id' => $donation->user->donorProfile?->donor_id ?? $this->generateDonorId(),
                'blood_group_status' => $donation->blood_group_verified ? 'staff_verified' : 'user_selected',
                'blood_group_verified' => $donation->blood_group_verified,
                'blood_group_verified_at' => $donation->blood_group_verified ? now() : null,
                'blood_group_verified_by' => $donation->blood_group_verified ? $recordedBy?->id : null,
                'next_eligible_donation_date' => $nextEligibleDate,
                'total_donations' => Donation::where('user_id', $donation->user_id)
                    ->where('status', 'completed')
                    ->count(),
            ]
        );
    }

    private function generateDonorId(): string
    {
        do {
            $donorId = 'DNR-' . now()->format('Y') . '-' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (\App\Models\DonorProfile::where('donor_id', $donorId)->exists());

        return $donorId;
    }
}
