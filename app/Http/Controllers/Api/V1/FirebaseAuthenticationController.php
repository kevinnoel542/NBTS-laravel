<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Auth\AuthenticateFirebaseUser;
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
    ): JsonResponse {
        $credentials = $request->credentials();
        $identity = $tokenVerifier->verify($credentials['firebase_id_token']);
        $user = $authenticateFirebaseUser->handle($identity);
        $expiresAt = now()->addDays((int) config('nbts.mobile_token_expiration_days', 30));

        $user->tokens()->where('name', $credentials['device_name'])->delete();

        $token = $user->createToken(
            name: $credentials['device_name'],
            abilities: ['donor:read', 'donor:write'],
            expiresAt: $expiresAt,
        );

        return response()->json([
            'token_type' => 'Bearer',
            'token' => $token->plainTextToken,
            'expires_at' => $expiresAt->toIso8601String(),
            'user' => new UserResource($user),
        ]);
    }
}
