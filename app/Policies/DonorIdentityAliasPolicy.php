<?php

namespace App\Policies;

use App\Models\DonorIdentityAlias;
use App\Models\User;
use App\PermissionName;

class DonorIdentityAliasPolicy
{
    public function view(User $user, DonorIdentityAlias $identityAlias): bool
    {
        return $user->can(PermissionName::ViewDonors->value)
            && ($user->hasDonorAccess($identityAlias->canonical_donor_id)
                || $user->hasDonorAccess($identityAlias->source_donor_id));
    }

    public function update(User $user, DonorIdentityAlias $identityAlias): bool
    {
        return false;
    }

    public function delete(User $user, DonorIdentityAlias $identityAlias): bool
    {
        return false;
    }
}
