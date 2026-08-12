<?php

namespace App\Policies;

use App\Models\BloodCenter;
use App\Models\DonorIdentityCheck;
use App\Models\User;
use App\PermissionName;

class DonorIdentityCheckPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::ViewDonors->value);
    }

    public function view(User $user, DonorIdentityCheck $identityCheck): bool
    {
        return $user->can(PermissionName::ViewDonors->value)
            && $user->hasCenterAccess($identityCheck->blood_center_id)
            && $user->hasDonorAccess($identityCheck->donor_id);
    }

    public function confirmAt(User $user, BloodCenter $bloodCenter): bool
    {
        return $user->can(PermissionName::ConfirmDonorIdentity->value)
            && $user->hasCenterAccess($bloodCenter);
    }

    public function delete(User $user, DonorIdentityCheck $identityCheck): bool
    {
        return false;
    }
}
