<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LeaderboardIndexRequest;
use App\Http\Resources\Api\V1\LeaderboardResource;
use App\Models\Leaderboard;
use App\Models\User;
use App\RoleName;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

final class LeaderboardController extends Controller
{
    public function __invoke(LeaderboardIndexRequest $request): AnonymousResourceCollection
    {
        $donor = $request->user();

        if (! $donor instanceof User) {
            abort(401);
        }

        abort_unless($donor->hasRole(RoleName::Donor->value), 403);
        Gate::forUser($donor)->authorize('viewAny', Leaderboard::class);

        $entries = Leaderboard::query()
            ->where('period', $request->period())
            ->whereHas(
                'user.donorProfile',
                fn (Builder $query): Builder => $query->where('share_anonymized_data', true),
            )
            ->with('user.donorProfile')
            ->orderBy('rank')
            ->orderBy('id')
            ->paginate($request->perPage())
            ->withQueryString();

        return LeaderboardResource::collection($entries)->additional([
            'meta' => [
                'current_user_rank' => $donor->leaderboardEntries()
                    ->where('period', $request->period())
                    ->value('rank'),
                'period' => $request->period(),
            ],
        ]);
    }
}
