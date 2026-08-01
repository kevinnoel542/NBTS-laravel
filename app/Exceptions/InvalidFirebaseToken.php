<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

final class InvalidFirebaseToken extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(trans('api.firebase_authentication_failed'));
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
        ], 401);
    }

    public function report(): bool
    {
        return false;
    }
}
