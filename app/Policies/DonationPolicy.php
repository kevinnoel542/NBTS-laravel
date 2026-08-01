<?php

namespace App\Policies;

use App\Models\BloodCenter;
use App\Models\Donation;
use App\Models\User;
use App\PermissionName;
use App\RoleName;

class DonationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_active && (
            $user->hasRole(RoleName::Donor->value)
            || $user->can(PermissionName::ViewDonations->value)
        );
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Donation $donation): bool
    {
        if ($user->hasRole(RoleName::Donor->value)) {
            return $user->id === $donation->user_id;
        }

        return $user->can(PermissionName::ViewDonations->value)
            && $user->hasCenterAccess($donation->blood_center_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionName::RecordDonations->value);
    }

    public function recordAt(User $user, BloodCenter $bloodCenter): bool
    {
        return $user->can(PermissionName::RecordDonations->value)
            && $user->hasCenterAccess($bloodCenter);
    }

    public function update(User $user, Donation $donation): bool
    {
        return $user->can(PermissionName::RecordDonations->value)
            && $user->hasCenterAccess($donation->blood_center_id);
    }

    public function delete(User $user, Donation $donation): bool
    {
        return $user->hasNationalScope()
            && $this->update($user, $donation);
    }
}
