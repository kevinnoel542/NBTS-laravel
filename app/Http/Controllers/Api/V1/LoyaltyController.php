<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Auth\EnsureDonorProfile;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\LoyaltyResource;
use App\Models\User;
use App\RoleName;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class LoyaltyController extends Controller
{
    public function __invoke(Request $request, EnsureDonorProfile $ensureDonorProfile): LoyaltyResource
    {
        $donor = $this->donor($request);
        $profile = $ensureDonorProfile->handle($donor);
        Gate::forUser($donor)->authorize('view', $profile);

        return new LoyaltyResource($donor->load([
            'donorBadges.badge',
            'donorProfile',
            'donorRewards.reward',
            'leaderboardEntries',
        ]));
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
