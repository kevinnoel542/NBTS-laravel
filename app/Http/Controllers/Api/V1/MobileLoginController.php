<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Auth\AuthenticateMobileDonor;
use App\Actions\Auth\IssueMobileToken;
use App\Actions\Profile\PrepareMobileUserResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\MobileLoginRequest;
use App\Http\Resources\Api\V1\UserResource;
use Illuminate\Http\JsonResponse;

final class MobileLoginController extends Controller
{
    public function __invoke(
        MobileLoginRequest $request,
        AuthenticateMobileDonor $authenticateMobileDonor,
        IssueMobileToken $issueMobileToken,
        PrepareMobileUserResource $prepareMobileUserResource,
    ): JsonResponse {
        $credentials = $request->credentials();
        $user = $authenticateMobileDonor->handle(
            $credentials['identifier'],
            $credentials['password'],
        );
        $token = $issueMobileToken->handle($user, $credentials['device_name']);

        return response()->json([
            'token_type' => 'Bearer',
            'token' => $token->plainTextToken,
            'expires_at' => $token->expiresAt->toIso8601String(),
            'user' => new UserResource($prepareMobileUserResource->handle($user)),
        ]);
    }
}
