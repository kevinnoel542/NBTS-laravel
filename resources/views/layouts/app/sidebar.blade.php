<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="operations-shell min-h-screen bg-[#f6f3f1] text-zinc-950 dark:bg-[#151414] dark:text-zinc-50">
        @php
            $workspaces = collect(config('operations.workspaces', []));
            $visibleWorkspaces = $workspaces->filter(
                fn (array $definition): bool => collect($definition['permissions'] ?? [])->contains(
                    fn (string $permission): bool => auth()->user()->can($permission),
                ),
            );
            $activeWorkspace = request()->route('workspace');
            $centerContext = app(\App\Services\ActiveCenterContext::class);
            $centerSelection = $centerContext->initialSelection(auth()->user());
        @endphp

        <flux:sidebar sticky collapsible class="operations-sidebar border-e border-zinc-200 bg-white dark:border-zinc-800 dark:bg-[#111010]">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse
                    :tooltip="__('console.header.toggle_navigation')"
                    data-test="sidebar-collapse"
                />
            </flux:sidebar.header>

            <flux:sidebar.nav class="operations-primary-navigation" :aria-label="__('console.navigation.primary')">
                <flux:sidebar.item
                    :href="route('dashboard')"
                    :current="request()->routeIs('dashboard')"
                    :tooltip="__('console.navigation.overview')"
                    class="operations-sidebar-link"
                    wire:navigate
                >
                    <x-slot:icon>
                        <x-public.icon name="layout-dashboard" :size="18" />
                    </x-slot:icon>
                    {{ __('console.navigation.overview') }}
                </flux:sidebar.item>

                @foreach (['workflow', 'coordination', 'system'] as $group)
                    @php($groupWorkspaces = $visibleWorkspaces->where('group', $group))
                    @if ($groupWorkspaces->isNotEmpty())
                        <div class="operations-sidebar-section-heading in-data-flux-sidebar-collapsed-desktop:hidden">
                            <span>{{ __('console.navigation.'.$group) }}</span>
                        </div>

                        @foreach ($groupWorkspaces as $slug => $definition)
                            <flux:sidebar.item
                                :href="route('operations.workspace', ['workspace' => $slug])"
                                :current="request()->routeIs('operations.workspace') && $activeWorkspace === $slug"
                                :tooltip="__($definition['title'])"
                                class="operations-sidebar-link"
                                wire:navigate
                            >
                                <x-slot:icon>
                                    <x-public.icon :name="$definition['icon']" :size="18" />
                                </x-slot:icon>
                                {{ __($definition['title']) }}
                            </flux:sidebar.item>
                        @endforeach
                    @endif
                @endforeach
            </flux:sidebar.nav>

            <flux:spacer />

            <div class="operations-sidebar-context in-data-flux-sidebar-collapsed-desktop:hidden">
                <span>{{ __('console.context.label') }}</span>
                <strong>{{ $centerContext->label(auth()->user(), $centerSelection) }}</strong>
                <small>{{ __('console.context.scope_note') }}</small>
            </div>

            <flux:sidebar.nav>
                <flux:sidebar.item
                    :href="route('profile.edit')"
                    :current="request()->routeIs('profile.edit', 'user-password.edit', 'appearance.edit', 'security.edit')"
                    :tooltip="__('console.navigation.account_settings')"
                    class="operations-sidebar-link"
                    wire:navigate
                >
                    <x-slot:icon>
                        <x-public.icon name="settings" :size="18" />
                    </x-slot:icon>
                    {{ __('console.navigation.account_settings') }}
                </flux:sidebar.item>
            </flux:sidebar.nav>

        </flux:sidebar>

        <flux:header class="operations-command-bar">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <div class="operations-command-bar__identity">
                <span>{{ __('console.navigation.command_center') }}</span>
                <small>{{ config('app.name') }}</small>
            </div>

            <flux:spacer />

            <div class="operations-language-switch" role="group" aria-label="{{ __('console.header.language') }}">
                @foreach (['en' => 'EN', 'sw' => 'SW'] as $locale => $label)
                    <form method="POST" action="{{ route('locale.update', $locale) }}">
                        @csrf
                        <button type="submit" @class(['is-active' => app()->getLocale() === $locale]) aria-pressed="{{ app()->getLocale() === $locale ? 'true' : 'false' }}">
                            <span aria-hidden="true">{{ $label }}</span>
                            <span class="sr-only">{{ $locale === 'en' ? __('English') : __('Kiswahili') }}</span>
                        </button>
                    </form>
                @endforeach
            </div>

            <button type="button" class="operations-command-button" x-data x-on:click="$flux.dark = ! $flux.dark" aria-label="{{ __('console.header.toggle_theme') }}">
                <x-public.icon name="moon" :size="17" />
            </button>

            <flux:dropdown position="bottom" align="end">
                <button type="button" class="operations-command-button" aria-label="{{ __('console.header.notifications') }}">
                    <x-public.icon name="bell" :size="17" />
                </button>

                <flux:menu class="p-0!">
                    <div class="operations-notification-menu">
                        <span class="operations-kicker">{{ __('console.header.notifications') }}</span>
                        <p>{{ __('console.header.notifications_description') }}</p>

                        @if (auth()->user()->can(\App\PermissionName::ManageNotifications->value))
                            <a href="{{ route('operations.workspace', ['workspace' => 'engagement', 'tab' => 'notifications']) }}" wire:navigate>
                                {{ __('console.header.open_notifications') }}
                                <x-public.icon name="chevron-right" :size="15" />
                            </a>
                        @endif
                    </div>
                </flux:menu>
            </flux:dropdown>

            <flux:dropdown position="bottom" align="end">
                <button type="button" class="operations-account-trigger" aria-label="{{ __('console.header.open_account_menu') }}">
                    <span class="operations-account-trigger__copy">
                        <small>{{ __('console.header.signed_in_as') }}</small>
                        <strong>{{ \Illuminate\Support\Str::before(auth()->user()->name, ' ') }}</strong>
                    </span>
                    <span class="operations-account-trigger__avatar">{{ auth()->user()->initials() }}</span>
                </button>

                <flux:menu>
                    <div class="p-2 text-sm font-normal">
                        <div class="flex items-center gap-3 rounded-lg bg-zinc-50 px-2 py-2 dark:bg-zinc-800">
                            <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" />
                            <div class="min-w-0 flex-1 text-start leading-tight">
                                <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                            </div>
                        </div>
                    </div>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('console.navigation.account_settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
