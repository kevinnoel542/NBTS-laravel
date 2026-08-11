<?php

namespace App\Livewire\Settings;

use App\Concerns\ProfileValidationRules;
use App\Concerns\ThrottlesSensitiveActions;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Profile settings')]
class Profile extends Component
{
    use ProfileValidationRules;
    use ThrottlesSensitiveActions;

    public string $name = '';

    public string $email = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $this->throttleSensitiveAction('profile-update');

        $user = Auth::user();

        $validated = $this->validate($this->profileRules($user->id));

        $user->fill($validated);

        $user->save();

        Flux::toast(variant: 'success', text: __('Profile updated.'));
    }
}
