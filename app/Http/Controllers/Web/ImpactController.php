<?php

namespace App\Http\Controllers\Web;

use App\DonationStatus;
use App\Http\Controllers\Controller;
use App\Models\BloodCenter;
use App\Models\Campaign;
use App\Models\Donation;
use App\Models\User;
use App\RoleName;
use Illuminate\Contracts\View\View;

final class ImpactController extends Controller
{
    public function __invoke(): View
    {
        $completedDonations = Donation::query()->where('status', DonationStatus::Completed);
        $totalDonations = (clone $completedDonations)->count();

        $stats = [
            'active_centers' => BloodCenter::query()->active()->count(),
            'active_campaigns' => Campaign::query()->publiclyVisible()->count(),
            'donors' => User::query()
                ->whereHas('roles', fn ($query) => $query->where('name', RoleName::Donor->value))
                ->count(),
            'lives_supported' => $totalDonations * 3,
            'total_donations' => $totalDonations,
            'volume_litres' => (int) round(((int) (clone $completedDonations)->sum('volume_ml')) / 1000),
        ];

        $bloodGroups = Donation::query()
            ->selectRaw('blood_group, count(*) as total')
            ->where('status', DonationStatus::Completed)
            ->groupBy('blood_group')
            ->orderByDesc('total')
            ->pluck('total', 'blood_group');

        return view('web.impact', compact('bloodGroups', 'stats'));
    }
}
