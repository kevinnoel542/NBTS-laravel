<?php

namespace App\Services;

use App\DonationStatus;
use App\Models\Donation;
use App\Models\User;

final class DonationSummaryService
{
    /**
     * @return array{
     *     total_donations: int,
     *     total_volume_ml: int,
     *     total_volume_liters: float,
     *     last_donation: string|null,
     *     lives_touched: int,
     *     lives_touched_is_estimate: bool
     * }
     */
    public function forDonor(User $donor): array
    {
        $completedDonations = Donation::query()
            ->where('user_id', $donor->id)
            ->where('status', DonationStatus::Completed);
        $totalDonations = (clone $completedDonations)->count();
        $totalVolumeMl = (int) (clone $completedDonations)->sum('volume_ml');
        $lastDonation = (clone $completedDonations)->max('donation_date');

        return [
            'total_donations' => $totalDonations,
            'total_volume_ml' => $totalVolumeMl,
            'total_volume_liters' => round($totalVolumeMl / 1000, 2),
            'last_donation' => is_string($lastDonation) ? $lastDonation : null,
            'lives_touched' => $totalDonations * 3,
            'lives_touched_is_estimate' => true,
        ];
    }
}
