<?php

namespace App\Policies;

use App\Models\BloodCenter;
use App\Models\OfflineCollectionDevice;
use App\Models\User;
use App\PermissionName;

class OfflineCollectionDevicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::ReconcileOfflineCollections->value);
    }

    public function view(User $user, OfflineCollectionDevice $device): bool
    {
        return $this->viewAny($user) && $user->hasCenterAccess($device->blood_center_id);
    }

    public function manageAt(User $user, BloodCenter $bloodCenter): bool
    {
        return $user->can(PermissionName::ManageOfflineCollectionDevices->value)
            && $user->hasCenterAccess($bloodCenter);
    }

    public function update(User $user, OfflineCollectionDevice $device): bool
    {
        return $user->can(PermissionName::ManageOfflineCollectionDevices->value)
            && $user->hasCenterAccess($device->blood_center_id);
    }

    public function delete(User $user, OfflineCollectionDevice $device): bool
    {
        return false;
    }
}
