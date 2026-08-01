<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Auth\EnsureDonorProfile;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\DonorCardResource;
use App\Models\User;
use App\RoleName;
use App\Services\DonationSummaryService;
use App\Services\DonorCardQrService;
use App\Services\DonorEligibilitySummaryService;
use Illuminate\Http\Request;

final class DonorCardController extends Controller
{
    public function __invoke(
        Request $request,
        EnsureDonorProfile $ensureDonorProfile,
        DonorCardQrService $donorCardQrService,
        DonorEligibilitySummaryService $eligibilitySummaryService,
        DonationSummaryService $donationSummaryService,
    ): DonorCardResource {
        $donor = $this->donor($request);
        $profile = $ensureDonorProfile->handle($donor)->load(['user', 'preferredCenter']);
        $qrCode = $donorCardQrService->issue($profile);

        return new DonorCardResource(
            resource: $profile,
            qrPayload: $qrCode['payload'],
            qrExpiresAt: $qrCode['expires_at'],
            eligibilitySummary: $eligibilitySummaryService->forDonor($donor),
            donationSummary: $donationSummaryService->forDonor($donor),
        );
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
