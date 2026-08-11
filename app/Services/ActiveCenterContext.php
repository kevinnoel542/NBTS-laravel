<?php

namespace App\Services;

use App\Models\BloodCenter;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

final class ActiveCenterContext
{
    /** @return Collection<int, BloodCenter> */
    public function availableCenters(User $user): Collection
    {
        return BloodCenter::query()
            ->active()
            ->visibleTo($user)
            ->orderBy('name')
            ->get();
    }

    public function initialSelection(User $user): string
    {
        $storedCenter = session('operations.center');
        $availableCenters = $this->availableCenters($user);

        if (is_numeric($storedCenter)
            && $availableCenters->contains('id', (int) $storedCenter)) {
            return (string) $storedCenter;
        }

        if ($user->hasNationalScope()) {
            return 'national';
        }

        $firstCenter = $availableCenters->first();

        return $firstCenter === null ? 'unassigned' : (string) $firstCenter->id;
    }

    public function setSelection(User $user, string $selection): string
    {
        $availableCenters = $this->availableCenters($user);

        if ($selection === 'national' && $user->hasNationalScope()) {
            session(['operations.center' => 'national']);

            return 'national';
        }

        if (ctype_digit($selection)
            && $availableCenters->contains('id', (int) $selection)) {
            session(['operations.center' => (int) $selection]);

            return $selection;
        }

        return $this->initialSelection($user);
    }

    public function selectedCenter(User $user, string $selection): ?BloodCenter
    {
        if ($selection === 'national' && $user->hasNationalScope()) {
            return null;
        }

        if (! ctype_digit($selection)) {
            return null;
        }

        return $this->availableCenters($user)->firstWhere('id', (int) $selection);
    }

    public function label(User $user, string $selection): string
    {
        if ($selection === 'national' && $user->hasNationalScope()) {
            return __('console.context.national');
        }

        $selectedCenter = $this->selectedCenter($user, $selection);

        return $selectedCenter instanceof BloodCenter
            ? $selectedCenter->name
            : __('console.context.no_assignment');
    }
}
