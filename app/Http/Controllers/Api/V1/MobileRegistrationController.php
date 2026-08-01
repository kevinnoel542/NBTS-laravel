<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Auth\CreateMobileDonor;
use App\Actions\Auth\IssueMobileToken;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\MobileRegistrationRequest;
use App\Http\Resources\Api\V1\UserResource;
use Illuminate\Http\JsonResponse;

final class MobileRegistrationController extends Controller
{
    public function __invoke(
        MobileRegistrationRequest $request,
        CreateMobileDonor $createMobileDonor,
        IssueMobileToken $issueMobileToken,
    ): JsonResponse {
        $user = $createMobileDonor->handle($request->registrationData());
        $token = $issueMobileToken->handle($user, $request->deviceName());

        return response()->json([
            'token_type' => 'Bearer',
            'token' => $token->plainTextToken,
            'expires_at' => $token->expiresAt->toIso8601String(),
            'user' => new UserResource($user),
        ], 201);
    }
}
