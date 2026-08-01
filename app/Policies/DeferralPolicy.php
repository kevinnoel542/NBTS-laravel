<?php

namespace App\Policies;

use App\Models\Deferral;
use App\Models\User;
use App\PermissionName;
use App\RoleName;

class DeferralPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_active && (
            $user->hasRole(RoleName::Donor->value)
            || $user->can(PermissionName::ManageDeferrals->value)
        );
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Deferral $deferral): bool
    {
        if ($user->id === $deferral->user_id) {
            return $user->is_active;
        }

        return $user->can(PermissionName::ManageDeferrals->value)
            && ($user->id === $deferral->created_by || $user->hasDonorAccess($deferral->user_id));
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionName::ManageDeferrals->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Deferral $deferral): bool
    {
        return $user->can(PermissionName::ManageDeferrals->value)
            && ($user->id === $deferral->created_by || $user->hasDonorAccess($deferral->user_id));
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Deferral $deferral): bool
    {
        return $this->update($user, $deferral);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function defer(User $user, User $donor): bool
    {
        return $user->can(PermissionName::ManageDeferrals->value)
            && $user->hasDonorAccess($donor);
    }
}
