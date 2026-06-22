<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Resources\DonationResource;

class DonationController extends Controller
{
    public function index(Request $request)
    {
        $donations = $request->user()->donations()->with(['bloodCenter', 'bloodUnit'])->latest('donation_date')->get();
        return DonationResource::collection($donations);
    }

    public function summary(Request $request)
    {
        $donations = $request->user()->donations()->where('status', 'completed');

        $totalDonations = (clone $donations)->count();
        $totalVolumeMl = (clone $donations)->sum('volume_ml');

        return response()->json([
            'data' => [
                'total_donations' => $totalDonations,
                'total_volume_ml' => (int) $totalVolumeMl,
                'total_volume_liters' => round($totalVolumeMl / 1000, 1),
                'lives_touched' => $totalDonations * 3,
            ],
        ]);
    }
}
