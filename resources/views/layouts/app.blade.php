<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main @class(['p-0!' => request()->routeIs('dashboard', 'operations.*', 'profile.edit', 'security.edit', 'appearance.edit')])>
        {{ $slot }}
    </flux:main>
</x-layouts::app.sidebar>
