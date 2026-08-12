<?php

namespace App\Policies;

use App\Models\BloodCenter;
use App\Models\CollectionEpisode;
use App\Models\User;
use App\PermissionName;

class CollectionEpisodePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::ViewDonations->value);
    }

    public function view(User $user, CollectionEpisode $collectionEpisode): bool
    {
        return $user->can(PermissionName::ViewDonations->value)
            && $user->hasCenterAccess($collectionEpisode->blood_center_id);
    }

    public function prepareAt(User $user, BloodCenter $bloodCenter): bool
    {
        return $user->can(PermissionName::PrepareCollections->value)
            && $user->hasCenterAccess($bloodCenter);
    }

    public function update(User $user, CollectionEpisode $collectionEpisode): bool
    {
        return $user->can(PermissionName::RecordDonations->value)
            && $user->hasCenterAccess($collectionEpisode->blood_center_id);
    }

    public function delete(User $user, CollectionEpisode $collectionEpisode): bool
    {
        return false;
    }
}
