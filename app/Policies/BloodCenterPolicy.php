<?php

namespace App\Policies;

use App\Models\BloodCenter;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BloodCenterPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, BloodCenter $bloodCenter): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function update(User $user, BloodCenter $bloodCenter): bool
    {
        return $user->role === 'admin';
    }

    public function delete(User $user, BloodCenter $bloodCenter): bool
    {
        return $user->role === 'admin';
    }
}
