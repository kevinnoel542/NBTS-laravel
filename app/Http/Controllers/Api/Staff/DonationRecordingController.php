<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use App\Http\Resources\DonationResource;
use App\Models\Donation;
use App\Models\User;
use App\Services\DonationRecordingService;
use Illuminate\Http\Request;

class DonationRecordingController extends Controller
{
    public function store(Request $request, DonationRecordingService $donationRecordingService)
    {
        if (!$request->user()->can('donations.record')) {
            abort(403);
        }

        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'blood_center_id' => 'required|exists:blood_centers,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'donation_type' => 'required|string|in:appointment,walk_in',
            'blood_group' => 'required|string|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'blood_group_verified' => 'sometimes|boolean',
            'volume_ml' => 'required|integer|min:250|max:600',
            'donation_date' => 'required|date|before_or_equal:today',
            'status' => 'sometimes|string|in:completed,failed',
            'notes' => 'nullable|string',
        ]);

        $donor = User::findOrFail($data['user_id']);

        if (!$donor->hasRole('donor')) {
            return response()->json(['message' => 'Selected user is not a donor'], 422);
        }

        if (($data['donation_type'] ?? null) === 'appointment' && empty($data['appointment_id'])) {
            return response()->json(['message' => 'Appointment donations require appointment_id'], 422);
        }

        if (($data['donation_type'] ?? null) === 'walk_in') {
            $data['appointment_id'] = null;
        }

        $data['blood_group_verified'] = (bool) ($data['blood_group_verified'] ?? false);

        $donation = $donationRecordingService->record($data, $request->user());

        return (new DonationResource($donation))->response()->setStatusCode(201);
    }

    public function verifyBloodGroup(Donation $donation, Request $request)
    {
        if (!$request->user()->can('donations.record')) {
            abort(403);
        }

        $data = $request->validate([
            'blood_group' => 'required|string|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
        ]);

        $donation->update([
            'blood_group' => $data['blood_group'],
            'blood_group_verified' => true,
        ]);

        $donation->user()->update([
            'blood_group' => $data['blood_group'],
        ]);

        $donation->user->donorProfile()->update([
            'blood_group_status' => 'staff_verified',
            'blood_group_verified' => true,
            'blood_group_verified_at' => now(),
            'blood_group_verified_by' => $request->user()->id,
        ]);

        return new DonationResource($donation->load(['user.donorProfile', 'bloodCenter', 'appointment']));
    }
}
