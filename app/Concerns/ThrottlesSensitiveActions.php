<?php

namespace App\Concerns;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

trait ThrottlesSensitiveActions
{
    /**
     * Count and guard a sensitive Livewire action before it mutates account state.
     *
     * @throws ValidationException
     */
    protected function throttleSensitiveAction(string $action, int $maxAttempts = 5): void
    {
        $identity = Auth::id() ?? request()->session()->getId();
        $key = 'sensitive-action:'.$action.'|'.$identity.'|'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            throw ValidationException::withMessages([
                'rateLimit' => __('Too many attempts. Please try again in :seconds seconds.', [
                    'seconds' => RateLimiter::availableIn($key),
                ]),
            ]);
        }

        RateLimiter::hit($key, 60);
    }
}
