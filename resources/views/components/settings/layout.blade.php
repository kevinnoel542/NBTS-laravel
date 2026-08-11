<div class="settings-workspace">
    <aside class="settings-navigation">
        <div class="settings-navigation__heading">
            <span>{{ __('Account controls') }}</span>
            <p>{{ __('Personal settings for this signed-in account.') }}</p>
        </div>

        <flux:navlist aria-label="{{ __('Settings') }}">
            <flux:navlist.item icon="user" :href="route('profile.edit')" wire:navigate>{{ __('Profile') }}</flux:navlist.item>
            <flux:navlist.item icon="shield-check" :href="route('security.edit')" wire:navigate>{{ __('Security') }}</flux:navlist.item>
            <flux:navlist.item icon="swatch" :href="route('appearance.edit')" wire:navigate>{{ __('Appearance') }}</flux:navlist.item>
        </flux:navlist>
    </aside>

    <article class="settings-content">
        <header class="settings-content__header">
            <div>
                <flux:heading size="lg">{{ $heading ?? '' }}</flux:heading>
                <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>
            </div>
        </header>

        <div class="settings-content__body">
            {{ $slot }}
        </div>
    </article>
</div>
