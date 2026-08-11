<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Profile\PrepareMobileUserResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

final class CurrentUserController extends Controller
{
    public function __invoke(
        Request $request,
        PrepareMobileUserResource $prepareMobileUserResource,
    ): UserResource {
        /** @var User $user */
        $user = $request->user();

        return new UserResource($prepareMobileUserResource->handle($user));
    }
}
