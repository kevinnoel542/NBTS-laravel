<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use App\Http\Resources\EligibilityResource;
use App\Models\Deferral;
use App\Models\User;
use App\Services\EligibilityService;
use Illuminate\Http\Request;

class EligibilityManagementController extends Controller
{
    public function check(User $donor, Request $request, EligibilityService $eligibilityService)
    {
        if (!$request->user()->can('donors.view') || !$donor->hasRole('donor')) {
            abort(403);
        }

        $data = $request->validate([
            'weight_kg' => 'nullable|numeric|min:20|max:250',
            'answers' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        $record = $eligibilityService->recordCheck($donor, $request->user(), $data);

        return response()->json([
            'data' => [
                'record_id' => $record->id,
                'eligibility' => (new EligibilityResource($eligibilityService->evaluate($donor)))->resolve(),
            ],
        ]);
    }

    public function defer(User $donor, Request $request, EligibilityService $eligibilityService)
    {
        if (!$request->user()->can('donors.manage') || !$donor->hasRole('donor')) {
            abort(403);
        }

        $data = $request->validate([
            'type' => 'required|string|in:temporary,permanent',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|required_if:type,temporary|date|after_or_equal:starts_at',
        ]);

        $deferral = $eligibilityService->defer($donor, $request->user(), $data);

        return response()->json(['data' => $deferral], 201);
    }

    public function liftDeferral(Deferral $deferral, Request $request, EligibilityService $eligibilityService)
    {
        if (!$request->user()->can('donors.manage')) {
            abort(403);
        }

        return response()->json([
            'data' => $eligibilityService->liftDeferral($deferral, $request->user()),
        ]);
    }
}
