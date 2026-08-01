<?php

namespace App\Actions\Auth;

use App\BloodGroupStatus;
use App\Models\DonorProfile;
use App\Models\User;
use Illuminate\Support\Str;

final class EnsureDonorProfile
{
    public function handle(User $user): DonorProfile
    {
        return $user->donorProfile()->firstOrCreate([], [
            'donor_id' => $this->generateDonorId(),
            'blood_group_status' => $user->blood_group === null
                ? BloodGroupStatus::Unknown
                : BloodGroupStatus::UserSelected,
            'language' => $user->locale,
        ]);
    }

    private function generateDonorId(): string
    {
        do {
            $donorId = 'DNR-'.now()->format('Y').'-'.Str::upper(Str::random(8));
        } while (DonorProfile::query()->where('donor_id', $donorId)->exists());

        return $donorId;
    }
}
