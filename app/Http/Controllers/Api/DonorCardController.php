<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DonorCardResource;
use App\Models\DonorProfile;
use Illuminate\Http\Request;

class DonorCardController extends Controller
{
    public function show(Request $request)
    {
        $profile = $request->user()->donorProfile()->firstOrCreate([], [
            'donor_id' => $this->generateDonorId(),
            'blood_group_status' => $request->user()->blood_group ? 'user_selected' : 'unknown',
        ]);

        return new DonorCardResource($profile->load('user'));
    }

    private function generateDonorId(): string
    {
        do {
            $donorId = 'DNR-' . now()->format('Y') . '-' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (DonorProfile::where('donor_id', $donorId)->exists());

        return $donorId;
    }
}
