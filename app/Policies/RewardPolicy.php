<?php

namespace App\Policies;

use App\Models\Reward;
use App\Models\User;
use App\PermissionName;

class RewardPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Reward $reward): bool
    {
        return $reward->is_active
            || ($user?->hasNationalScope() === true && $user->can(PermissionName::ManageLoyalty->value));
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
    public function update(User $user, Reward $reward): bool
    {
        return $user->hasNationalScope()
            && $user->can(PermissionName::ManageLoyalty->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Reward $reward): bool
    {
        return $user->hasNationalScope()
            && $user->can(PermissionName::ManageLoyalty->value);
    }
}
