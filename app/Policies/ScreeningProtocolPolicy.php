<?php

namespace App\Policies;

use App\Models\ScreeningProtocol;
use App\Models\User;
use App\PermissionName;

class ScreeningProtocolPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::CheckEligibility->value);
    }

    public function view(User $user, ScreeningProtocol $screeningProtocol): bool
    {
        return $this->viewAny($user);
    }

    public function manage(User $user): bool
    {
        return $user->can(PermissionName::ManageScreeningProtocols->value);
    }

    public function delete(User $user, ScreeningProtocol $screeningProtocol): bool
    {
        return false;
    }
}
