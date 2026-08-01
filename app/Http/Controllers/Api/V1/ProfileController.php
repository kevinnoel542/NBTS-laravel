<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Profile\UpdateMobileDonorProfile;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateProfileRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

final class ProfileController extends Controller
{
    public function show(Request $request): UserResource
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(401);
        }

        return new UserResource($user->load(['roles', 'donorProfile.preferredCenter']));
    }

    public function update(
        UpdateProfileRequest $request,
        UpdateMobileDonorProfile $updateMobileDonorProfile,
    ): UserResource {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(401);
        }

        return new UserResource($updateMobileDonorProfile->handle($user, $request->profileData()));
    }
}
