<?php

namespace App\Policies;

use App\Models\DonorDuplicateCase;
use App\Models\User;
use App\PermissionName;

class DonorDuplicateCasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::ViewDonors->value);
    }

    public function view(User $user, DonorDuplicateCase $duplicateCase): bool
    {
        return $user->can(PermissionName::ReviewDonorDuplicates->value)
            && ($duplicateCase->blood_center_id === null
                ? $user->hasNationalScope()
                : $user->hasCenterAccess($duplicateCase->blood_center_id));
    }

    public function review(User $user, DonorDuplicateCase $duplicateCase): bool
    {
        return $this->view($user, $duplicateCase)
            && $user->can(PermissionName::ManageDonors->value);
    }

    public function delete(User $user, DonorDuplicateCase $duplicateCase): bool
    {
        return false;
    }
}
