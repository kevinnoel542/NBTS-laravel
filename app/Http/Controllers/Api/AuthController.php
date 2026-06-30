<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\UserResource;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'blood_group' => 'required|string|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'gender' => 'required|string|in:male,female,other,Male,Female,Other',
            'region' => 'required|string',
            'date_of_birth' => 'required|date',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'blood_group' => $request->blood_group,
            'gender' => strtolower($request->gender),
            'region' => $request->region,
            'date_of_birth' => $request->date_of_birth,
            'role' => 'donor',
        ]);

        $user->assignRole('donor');
        $user->donorProfile()->create([
            'donor_id' => $this->generateDonorId(),
            'blood_group_status' => 'user_selected',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user->load(['roles', 'donorProfile.preferredCenter', 'donations'])),
        ], 201);
    }

    public function login(Request $request)
    {
        if (!$request->filled('identifier')) {
            $request->merge([
                'identifier' => $request->input('email', $request->input('phone')),
            ]);
        }

        $request->validate([
            'identifier' => 'required|string',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->identifier)
            ->orWhere('phone', $request->identifier)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'identifier' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user->load(['roles', 'donorProfile.preferredCenter', 'donations'])),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function profile(Request $request)
    {
        return new UserResource($request->user()->load(['roles', 'donorProfile.preferredCenter', 'donations']));
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|unique:users,phone,' . $user->id,
            'blood_group' => 'sometimes|string|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'gender' => 'sometimes|string|in:male,female,other',
            'date_of_birth' => 'sometimes|date',
            'region' => 'sometimes|string',
            'address' => 'sometimes|string',
            'preferred_center_id' => 'sometimes|nullable|exists:blood_centers,id',
            'emergency_contact_name' => 'sometimes|nullable|string|max:255',
            'emergency_contact_phone' => 'sometimes|nullable|string|max:255',
            'push_notifications_enabled' => 'sometimes|boolean',
            'sms_reminders_enabled' => 'sometimes|boolean',
            'share_anonymized_data' => 'sometimes|boolean',
            'language' => 'sometimes|string|in:English,Swahili',
        ]);

        $profileData = collect($data)->only([
            'preferred_center_id',
            'emergency_contact_name',
            'emergency_contact_phone',
            'push_notifications_enabled',
            'sms_reminders_enabled',
            'share_anonymized_data',
            'language',
        ])->all();

        $userData = collect($data)->except(array_keys($profileData))->all();

        $user->update($userData);

        if ($profileData !== []) {
            $user->donorProfile()->updateOrCreate(
                ['user_id' => $user->id],
                array_merge([
                    'donor_id' => $user->donorProfile?->donor_id ?? $this->generateDonorId(),
                ], $profileData)
            );
        }

        return new UserResource($user->load(['roles', 'donorProfile.preferredCenter', 'donations']));
    }

    private function generateDonorId(): string
    {
        do {
            $donorId = 'DNR-' . now()->format('Y') . '-' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (\App\Models\DonorProfile::where('donor_id', $donorId)->exists());

        return $donorId;
    }
}
