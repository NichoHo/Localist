@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center rounded-full bg-accent-soft px-3.5 py-1.5 text-sm font-semibold text-ink focus:outline-none transition-colors duration-150'
            : 'inline-flex items-center rounded-full px-3.5 py-1.5 text-sm font-medium text-ink-muted hover:bg-canvas-2 hover:text-ink focus:outline-none transition-colors duration-150';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
