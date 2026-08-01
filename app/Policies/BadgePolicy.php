<?php

namespace App\Policies;

use App\Models\Badge;
use App\Models\User;
use App\PermissionName;

class BadgePolicy
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
    public function view(?User $user, Badge $badge): bool
    {
        return $badge->is_active
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
    public function update(User $user, Badge $badge): bool
    {
        return $user->hasNationalScope()
            && $user->can(PermissionName::ManageLoyalty->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Badge $badge): bool
    {
        return $user->hasNationalScope()
            && $user->can(PermissionName::ManageLoyalty->value);
    }
}
