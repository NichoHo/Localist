@props(['active'])

{{-- Same dark-band context as nav-link.blade.php, see the note there. --}}
@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-accent text-start text-base font-medium text-header-ink bg-white/5 focus:outline-none transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-header-ink/65 hover:text-header-ink hover:bg-white/5 hover:border-header-line focus:outline-none focus:text-header-ink focus:bg-white/5 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
