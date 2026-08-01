<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\DonationResource;
use App\Models\Donation;
use App\Models\User;
use App\RoleName;
use App\Services\DonationSummaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

final class DonationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $donor = $this->donor($request);
        Gate::forUser($donor)->authorize('viewAny', Donation::class);
        $perPage = min(max($request->integer('per_page', 20), 1), 50);

        $donations = Donation::query()
            ->where('user_id', $donor->id)
            ->with('bloodCenter')
            ->latest('donation_date')
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        return DonationResource::collection($donations);
    }

    public function summary(Request $request, DonationSummaryService $donationSummaryService): JsonResponse
    {
        $donor = $this->donor($request);
        Gate::forUser($donor)->authorize('viewAny', Donation::class);

        return response()->json([
            'data' => $donationSummaryService->forDonor($donor),
        ]);
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
