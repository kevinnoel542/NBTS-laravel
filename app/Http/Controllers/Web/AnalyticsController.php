<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\BloodCenter;
use App\Models\Campaign;
use App\Models\BloodInventory;
use App\Models\BloodUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index()
    {
        // Summary Stats
        $totalDonations = Donation::count();
        $totalVolume = Donation::sum('volume_ml') / 1000; // In Liters
        $livesSaved = $totalDonations * 3;
        $activeCampaigns = Campaign::where('status', 'active')->count();
        $availableUnits = BloodUnit::where('status', 'available')->count();
        $lowStockGroups = BloodInventory::whereColumn('available_units', '<', 'minimum_threshold')->count();

        // Blood Group Distribution
        $bloodGroupStats = Donation::select('blood_group', DB::raw('count(*) as total'))
            ->groupBy('blood_group')
            ->pluck('total', 'blood_group')
            ->toArray();

        // Monthly Trends (Last 6 months)
        $monthlyTrends = Donation::select(
                DB::raw('DATE_FORMAT(donation_date, "%b") as month'),
                DB::raw('count(*) as total')
            )
            ->groupBy('month')
            ->orderBy('donation_date', 'asc')
            ->limit(6)
            ->get();

        return view('web.analytics', compact(
            'totalDonations',
            'totalVolume',
            'livesSaved',
            'activeCampaigns',
            'availableUnits',
            'lowStockGroups',
            'bloodGroupStats',
            'monthlyTrends'
        ));
    }
}
