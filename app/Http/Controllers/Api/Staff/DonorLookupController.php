<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use App\Http\Resources\DonorProfileResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\DonorQrCodeService;
use Illuminate\Http\Request;

class DonorLookupController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->user()->can('donors.view')) {
            abort(403);
        }

        $data = $request->validate([
            'query' => 'required|string|min:2',
            'type' => 'nullable|string|in:any,donor_id,phone,name,email,qr',
        ]);

        $query = $data['query'];
        $type = $data['type'] ?? 'any';

        $donors = User::query()
            ->with('donorProfile')
            ->role('donor')
            ->when(in_array($type, ['any', 'phone'], true), function ($builder) use ($query, $type) {
                $builder->when($type === 'phone', fn ($q) => $q->where('phone', 'like', "%{$query}%"));
            })
            ->where(function ($builder) use ($query, $type) {
                if (in_array($type, ['any', 'phone'], true)) {
                    $builder->orWhere('phone', 'like', "%{$query}%");
                }

                if (in_array($type, ['any', 'name'], true)) {
                    $builder->orWhere('name', 'like', "%{$query}%");
                }

                if (in_array($type, ['any', 'email'], true)) {
                    $builder->orWhere('email', 'like', "%{$query}%");
                }

                if (in_array($type, ['any', 'donor_id', 'qr'], true)) {
                    $builder->orWhereHas('donorProfile', fn ($profile) => $profile->where('donor_id', $query));
                }
            })
            ->limit(20)
            ->get();

        return UserResource::collection($donors);
    }

    public function show(User $donor, Request $request)
    {
        if (!$request->user()->can('donors.view') || !$donor->hasRole('donor')) {
            abort(403);
        }

        return new UserResource($donor->load('donorProfile'));
    }

    public function scan(Request $request, DonorQrCodeService $qrCodeService)
    {
        if (!$request->user()->can('donors.view')) {
            abort(403);
        }

        $data = $request->validate([
            'qr_payload' => 'required|string',
        ]);

        $profile = $qrCodeService->verify($data['qr_payload']);
        $donor = $profile->user;

        if (!$donor?->hasRole('donor')) {
            return response()->json(['message' => 'Donor account was not found'], 404);
        }

        return response()->json([
            'data' => [
                'valid' => true,
                'donor' => new UserResource($donor->load('donorProfile.preferredCenter')),
                'donor_profile' => new DonorProfileResource($profile),
                'scan_result' => [
                    'donor_id' => $profile->donor_id,
                    'blood_group_verified' => (bool) $profile->blood_group_verified,
                    'eligibility_status' => $profile->eligibility_status,
                    'next_eligible_donation_date' => $profile->next_eligible_donation_date,
                    'total_donations' => $profile->total_donations,
                ],
            ],
        ]);
    }
}
