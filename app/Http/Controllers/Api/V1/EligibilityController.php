<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Auth\EnsureDonorProfile;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\EligibilityResource;
use App\Models\User;
use App\RoleName;
use App\Services\DonorEligibilitySummaryService;
use Illuminate\Http\Request;

final class EligibilityController extends Controller
{
    public function __invoke(
        Request $request,
        EnsureDonorProfile $ensureDonorProfile,
        DonorEligibilitySummaryService $eligibilitySummaryService,
    ): EligibilityResource {
        $donor = $this->donor($request);
        $ensureDonorProfile->handle($donor);

        return new EligibilityResource($eligibilitySummaryService->forDonor($donor));
    }

    private function donor(Request $request): User
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(401);
        }

        abort_unless($user->hasRole(RoleName::Donor->value), 403);

        return $user;
    }
}
