@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full rounded-xl bg-accent-soft px-4 py-2.5 text-start text-base font-semibold text-ink focus:outline-none transition-colors duration-150'
            : 'block w-full rounded-xl px-4 py-2.5 text-start text-base font-medium text-ink-muted hover:bg-canvas-2 hover:text-ink focus:outline-none transition-colors duration-150';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
