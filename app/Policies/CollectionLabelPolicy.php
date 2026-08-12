<?php

namespace App\Policies;

use App\Models\CollectionLabel;
use App\Models\User;
use App\PermissionName;

class CollectionLabelPolicy
{
    public function view(User $user, CollectionLabel $collectionLabel): bool
    {
        return $user->can(PermissionName::ViewDonations->value)
            && $user->hasCenterAccess($collectionLabel->collectionEpisode->blood_center_id);
    }

    public function manage(User $user, CollectionLabel $collectionLabel): bool
    {
        return $user->can(PermissionName::ManageCollectionLabels->value)
            && $user->hasCenterAccess($collectionLabel->collectionEpisode->blood_center_id);
    }

    public function delete(User $user, CollectionLabel $collectionLabel): bool
    {
        return false;
    }
}
