<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Profile\PrepareMobileUserResource;
use App\Actions\Profile\UpdateMobileDonorProfile;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateProfileRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

final class ProfileController extends Controller
{
    public function show(
        Request $request,
        PrepareMobileUserResource $prepareMobileUserResource,
    ): UserResource {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(401);
        }

        return new UserResource($prepareMobileUserResource->handle($user));
    }

    public function update(
        UpdateProfileRequest $request,
        UpdateMobileDonorProfile $updateMobileDonorProfile,
        PrepareMobileUserResource $prepareMobileUserResource,
    ): UserResource {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(401);
        }

        $updatedUser = $updateMobileDonorProfile->handle($user, $request->profileData());

        return new UserResource($prepareMobileUserResource->handle($updatedUser));
    }
}
