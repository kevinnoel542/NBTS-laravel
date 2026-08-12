<?php

namespace App\Policies;

use App\Models\OfflineCollectionSubmission;
use App\Models\User;
use App\PermissionName;

class OfflineCollectionSubmissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::ReconcileOfflineCollections->value);
    }

    public function view(User $user, OfflineCollectionSubmission $submission): bool
    {
        return $this->viewAny($user) && $user->hasCenterAccess($submission->blood_center_id);
    }

    public function reconcile(User $user, OfflineCollectionSubmission $submission): bool
    {
        return $this->view($user, $submission);
    }

    public function delete(User $user, OfflineCollectionSubmission $submission): bool
    {
        return false;
    }
}
