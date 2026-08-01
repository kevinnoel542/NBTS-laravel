<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Sanctum\PersonalAccessToken;

final class LogoutController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $bearerToken = $request->bearerToken();

        if (! $user instanceof User || $bearerToken === null) {
            return response()->noContent();
        }

        $accessToken = PersonalAccessToken::findToken($bearerToken);

        if ($accessToken !== null
            && $accessToken->tokenable()->whereKey($user->getKey())->exists()) {
            $accessToken->delete();
        }

        return response()->noContent();
    }
}
