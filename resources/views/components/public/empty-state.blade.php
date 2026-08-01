@props([
    'copy',
    'icon' => 'search-x',
    'title',
])

<div {{ $attributes->class('empty-state') }}>
    <x-public.icon :name="$icon" :size="28" />
    <h2>{{ $title }}</h2>
    <p>{{ $copy }}</p>
    {{ $slot }}
</div>
