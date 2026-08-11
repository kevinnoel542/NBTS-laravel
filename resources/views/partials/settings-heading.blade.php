<div class="operations-page-header settings-page-header">
    <div>
        <p class="operations-kicker">{{ __('Account center') }}</p>
        <h1>{{ __('Settings') }}</h1>
        <p>{{ __('Manage your identity, sign-in security, trusted devices, and workspace appearance.') }}</p>
    </div>

    <div class="operations-context-card">
        <div class="operations-context-card__icon">
            <flux:icon.shield-check class="size-4" />
        </div>
        <div>
            <span>{{ __('Signed in as') }}</span>
            <strong>{{ auth()->user()->email }}</strong>
        </div>
    </div>
</div>
