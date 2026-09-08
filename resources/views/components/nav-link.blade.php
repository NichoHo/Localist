@props(['active'])

{{-- Sits on the fixed dark header band (see .trade-band), so it uses --header-*
     tokens, not the theme-swapping --ink ones. --}}
@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-accent text-sm font-medium leading-5 text-header-ink focus:outline-none transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-header-ink/65 hover:text-header-ink hover:border-header-line focus:outline-none focus:text-header-ink focus:border-header-line transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
