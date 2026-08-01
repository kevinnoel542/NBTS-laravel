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

        <flux:sidebar sticky collapsible="mobile" class="operations-sidebar border-e border-zinc-200 bg-white dark:border-zinc-800 dark:bg-[#111010]">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('console.navigation.overview')" class="grid">
                    <flux:sidebar.item :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        <span class="operations-nav-label">
                            <x-public.icon name="layout-dashboard" :size="18" />
                            <span>{{ __('console.navigation.overview') }}</span>
                        </span>
                    </flux:sidebar.item>
                </flux:sidebar.group>

                @foreach (['workflow', 'coordination', 'system'] as $group)
                    @php($groupWorkspaces = $visibleWorkspaces->where('group', $group))
                    @if ($groupWorkspaces->isNotEmpty())
                        <flux:sidebar.group :heading="__('console.navigation.'.$group)" class="grid">
                            @foreach ($groupWorkspaces as $slug => $definition)
                                <flux:sidebar.item
                                    :href="route('operations.workspace', ['workspace' => $slug])"
                                    :current="request()->routeIs('operations.workspace') && $activeWorkspace === $slug"
                                    wire:navigate
                                >
                                    <span class="operations-nav-label">
                                        <x-public.icon :name="$definition['icon']" :size="18" />
                                        <span>{{ __($definition['title']) }}</span>
                                    </span>
                                </flux:sidebar.item>
                            @endforeach
                        </flux:sidebar.group>
                    @endif
                @endforeach
            </flux:sidebar.nav>

            <flux:spacer />

            <div class="operations-sidebar-context">
                <span>{{ __('console.context.label') }}</span>
                <strong>{{ $centerContext->label(auth()->user(), $centerSelection) }}</strong>
                <small>{{ __('console.context.scope_note') }}</small>
            </div>

            <flux:sidebar.nav>
                <flux:sidebar.item :href="route('profile.edit')" :current="request()->routeIs('profile.edit', 'user-password.edit', 'appearance.edit', 'security.edit')" wire:navigate>
                    <span class="operations-nav-label">
                        <x-public.icon name="settings" :size="18" />
                        <span>{{ __('console.navigation.account_settings') }}</span>
                    </span>
                </flux:sidebar.item>
            </flux:sidebar.nav>

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <form method="POST" action="{{ route('locale.update', app()->getLocale() === 'en' ? 'sw' : 'en') }}" class="w-full">
                            @csrf
                            <flux:menu.item as="button" type="submit" icon="language" class="w-full cursor-pointer">
                                {{ app()->getLocale() === 'en' ? __('Kiswahili') : __('English') }}
                            </flux:menu.item>
                        </form>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
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
