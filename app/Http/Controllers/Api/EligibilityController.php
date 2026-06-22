<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EligibilityResource;
use App\Services\EligibilityService;
use Illuminate\Http\Request;

class EligibilityController extends Controller
{
    public function show(Request $request, EligibilityService $eligibilityService)
    {
        return new EligibilityResource($eligibilityService->evaluate($request->user()));
    }
}
