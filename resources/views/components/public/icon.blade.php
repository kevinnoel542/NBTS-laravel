@props([
    'name',
    'size' => 20,
])

<span {{ $attributes->class('ui-icon') }} aria-hidden="true">
    <i data-lucide="{{ $name }}" width="{{ $size }}" height="{{ $size }}"></i>
</span>
