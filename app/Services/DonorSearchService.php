<?php

namespace App\Services;

use App\Models\BloodCenter;
use App\Models\User;
use App\PermissionName;
use App\RoleName;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

final readonly class DonorSearchService
{
    public function __construct(private DonorCardQrService $donorCardQrService) {}

    /**
     * @return Collection<int, User>
     */
    public function search(
        User $actor,
        string $search,
        ?BloodCenter $bloodCenter = null,
        int $limit = 20,
    ): Collection {
        if (! $actor->can(PermissionName::ViewDonors->value)) {
            throw new AuthorizationException;
        }

        $search = trim($search);

        if ($search === '') {
            throw ValidationException::withMessages([
                'search' => [__('validation.required', ['attribute' => __('search')])],
            ]);
        }

        if (str_starts_with($search, 'nbtsqr.')) {
            return $this->fromQrPayload($actor, $search, $bloodCenter);
        }

        $this->authorizeCenterScope($actor, $bloodCenter);
        $escapedSearch = addcslashes($search, '\\%_');
        $pattern = "%{$escapedSearch}%";

        return User::query()
            ->active()
            ->whereHas('roles', fn (Builder $query): Builder => $query->where('name', RoleName::Donor->value))
            ->when(
                ! $actor->hasNationalScope(),
                fn (Builder $query): Builder => $this->scopeToCenter($query, $bloodCenter),
            )
            ->where(function (Builder $query) use ($pattern): void {
                $query
                    ->where('name', 'like', $pattern)
                    ->orWhere('email', 'like', $pattern)
                    ->orWhere('phone', 'like', $pattern)
                    ->orWhereHas('donorProfile', fn (Builder $profileQuery): Builder => $profileQuery->where('donor_id', 'like', $pattern));
            })
            ->with(['donorProfile.preferredCenter'])
            ->orderBy('name')
            ->limit(max(1, min($limit, 50)))
            ->get();
    }

    /** @return Collection<int, User> */
    private function fromQrPayload(User $actor, string $payload, ?BloodCenter $bloodCenter): Collection
    {
        $this->authorizeCenterScope($actor, $bloodCenter);
        $profile = $this->donorCardQrService->verify($payload);

        return new Collection([$profile->user->load('donorProfile.preferredCenter')]);
    }

    private function authorizeCenterScope(User $actor, ?BloodCenter $bloodCenter): void
    {
        if ($actor->hasNationalScope()) {
            return;
        }

        if ($bloodCenter === null || ! $actor->hasCenterAccess($bloodCenter)) {
            throw new AuthorizationException;
        }
    }

    /**
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    private function scopeToCenter(Builder $query, ?BloodCenter $bloodCenter): Builder
    {
        $bloodCenterId = $bloodCenter?->id;

        return $query->where(function (Builder $centerQuery) use ($bloodCenterId): void {
            $centerQuery
                ->whereHas('donorProfile', fn (Builder $profileQuery): Builder => $profileQuery->where('preferred_center_id', $bloodCenterId))
                ->orWhereHas('appointments', fn (Builder $appointmentQuery): Builder => $appointmentQuery->where('blood_center_id', $bloodCenterId))
                ->orWhereHas('donations', fn (Builder $donationQuery): Builder => $donationQuery->where('blood_center_id', $bloodCenterId));
        });
    }
}
