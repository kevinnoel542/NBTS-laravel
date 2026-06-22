<?php

namespace App\Services;

use App\Models\BloodInventory;
use App\Models\BloodUnit;
use App\Models\Campaign;
use App\Models\Donation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReportsService
{
    public function summary(): array
    {
        return [
            'donors' => User::role('donor')->count(),
            'donations' => Donation::where('status', 'completed')->count(),
            'blood_units_available' => BloodUnit::where('status', 'available')->count(),
            'low_stock_groups' => BloodInventory::whereColumn('available_units', '<', 'minimum_threshold')->count(),
            'active_campaigns' => Campaign::whereIn('status', ['ongoing', 'upcoming'])->count(),
        ];
    }

    public function donationReport(): array
    {
        return [
            'by_blood_group' => Donation::select('blood_group', DB::raw('COUNT(*) as total'))
                ->where('status', 'completed')
                ->groupBy('blood_group')
                ->pluck('total', 'blood_group'),
            'monthly' => Donation::select(DB::raw('DATE_FORMAT(donation_date, "%Y-%m") as month'), DB::raw('COUNT(*) as total'))
                ->where('status', 'completed')
                ->groupBy('month')
                ->orderBy('month')
                ->get(),
        ];
    }

    public function inventoryReport(): array
    {
        return [
            'inventory' => BloodInventory::with('bloodCenter')
                ->orderBy('blood_center_id')
                ->orderBy('blood_group')
                ->get(),
            'unit_statuses' => BloodUnit::select('status', DB::raw('COUNT(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status'),
        ];
    }
}
