<?php

namespace App\Actions\Profile;

use App\Models\User;

final class PrepareMobileUserResource
{
    public function handle(User $user): User
    {
        return $user
            ->loadMissing(['roles', 'donorProfile.preferredCenter'])
            ->loadSum('completedDonations', 'volume_ml');
    }
}
