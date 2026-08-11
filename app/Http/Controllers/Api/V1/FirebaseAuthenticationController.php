<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Auth\AuthenticateFirebaseUser;
use App\Actions\Auth\IssueMobileToken;
use App\Actions\Profile\PrepareMobileUserResource;
use App\Firebase\FirebaseTokenVerifier;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\FirebaseAuthenticationRequest;
use App\Http\Resources\Api\V1\UserResource;
use Illuminate\Http\JsonResponse;

final class FirebaseAuthenticationController extends Controller
{
    public function __invoke(
        FirebaseAuthenticationRequest $request,
        FirebaseTokenVerifier $tokenVerifier,
        AuthenticateFirebaseUser $authenticateFirebaseUser,
        IssueMobileToken $issueMobileToken,
        PrepareMobileUserResource $prepareMobileUserResource,
    ): JsonResponse {
        $credentials = $request->credentials();
        $identity = $tokenVerifier->verify($credentials['firebase_id_token']);
        $user = $authenticateFirebaseUser->handle($identity);
        $token = $issueMobileToken->handle($user, $credentials['device_name']);

        return response()->json([
            'token_type' => 'Bearer',
            'token' => $token->plainTextToken,
            'expires_at' => $token->expiresAt->toIso8601String(),
            'user' => new UserResource($prepareMobileUserResource->handle($user)),
        ]);
    }
}
