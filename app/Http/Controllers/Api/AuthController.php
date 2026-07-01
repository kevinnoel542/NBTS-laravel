<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\FirebaseAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users',
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

    public function firebase(Request $request, FirebaseAuthService $firebaseAuth)
    {
        $data = $request->validate([
            'firebase_id_token' => 'required_without:id_token|string',
            'id_token' => 'required_without:firebase_id_token|string',
            'provider' => 'nullable|string|in:google.com,apple.com,microsoft.com,google,apple,microsoft',
            'email' => 'nullable|email|max:255',
            'name' => 'nullable|string|max:255',
            'photo_url' => 'nullable|url|max:2048',
            'firebase_uid' => 'nullable|string|max:128',
        ]);

        $claims = $firebaseAuth->verifyIdToken($data['firebase_id_token'] ?? $data['id_token']);
        $firebase = $claims['firebase'] ?? [];
        $provider = $firebase['sign_in_provider'] ?? $data['provider'] ?? null;
        $uid = $claims['sub'];
        $email = $claims['email'] ?? $data['email'] ?? null;
        $name = $claims['name'] ?? $data['name'] ?? null;
        $photoUrl = $claims['picture'] ?? $data['photo_url'] ?? null;

        if (($data['firebase_uid'] ?? null) && $data['firebase_uid'] !== $uid) {
            throw ValidationException::withMessages([
                'firebase_uid' => ['Firebase UID does not match the verified token.'],
            ]);
        }

        if (! is_string($email) || $email === '') {
            throw ValidationException::withMessages([
                'email' => ['Firebase account did not provide an email address.'],
            ]);
        }

        $user = DB::transaction(function () use ($uid, $provider, $email, $name, $photoUrl, $claims): User {
            $user = User::where('firebase_uid', $uid)->first()
                ?? User::where('email', $email)->first();

            if ($user && $user->firebase_uid && $user->firebase_uid !== $uid) {
                throw ValidationException::withMessages([
                    'firebase_id_token' => ['This email is already linked to a different Firebase account.'],
                ]);
            }

            if (! $user) {
                $user = User::create([
                    'name' => $name ?: Str::before($email, '@'),
                    'email' => $email,
                    'email_verified_at' => ($claims['email_verified'] ?? false) ? now() : null,
                    'password' => Hash::make(Str::random(48)),
                    'profile_photo_path' => $photoUrl,
                    'role' => 'donor',
                    'is_active' => true,
                    'firebase_uid' => $uid,
                    'firebase_provider' => $provider,
                ]);
            } else {
                $user->forceFill([
                    'firebase_uid' => $user->firebase_uid ?: $uid,
                    'firebase_provider' => $provider,
                    'email_verified_at' => $user->email_verified_at ?: (($claims['email_verified'] ?? false) ? now() : null),
                    'profile_photo_path' => $user->profile_photo_path ?: $photoUrl,
                ])->save();
            }

            if (! $user->hasRole('donor')) {
                $user->assignRole('donor');
            }

            $user->donorProfile()->firstOrCreate([], [
                'donor_id' => $this->generateDonorId(),
                'blood_group_status' => $user->blood_group ? 'user_selected' : 'unknown',
            ]);

            return $user;
        });

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
            'language' => 'sometimes|string|in:en,sw,English,Swahili',
        ]);

        if (isset($data['language'])) {
            $data['language'] = match ($data['language']) {
                'English' => 'en',
                'Swahili' => 'sw',
                default => $data['language'],
            };
        }

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
