<?php

namespace App\Policies;

use App\DonorRewardStatus;
use App\Models\DonorReward;
use App\Models\User;
use App\PermissionName;

class DonorRewardPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DonorReward $donorReward): bool
    {
        return $user->is_active && (
            $user->id === $donorReward->user_id
            || ($user->hasNationalScope() && $user->can(PermissionName::ManageLoyalty->value))
        );
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasNationalScope()
            && $user->can(PermissionName::ManageLoyalty->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DonorReward $donorReward): bool
    {
        return $user->hasNationalScope()
            && $user->can(PermissionName::ManageLoyalty->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DonorReward $donorReward): bool
    {
        return $user->hasNationalScope()
            && $user->can(PermissionName::ManageLoyalty->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function redeem(User $user, DonorReward $donorReward): bool
    {
        return $user->is_active
            && $user->id === $donorReward->user_id
            && $donorReward->status === DonorRewardStatus::Earned;
    }
}
