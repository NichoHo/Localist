@props(['slug' => '', 'class' => 'size-6'])

<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}" aria-hidden="true">
    @switch($slug)
        @case('restaurants')
            <path d="M3 2v7c0 1.1.9 2 2 2h1a2 2 0 0 0 2-2V2M7 2v20M3 2v7" />
            <path d="M17 2a5 5 0 0 0-5 5v3a2 2 0 0 0 2 2h3v10" />
            @break
        @case('cafes-coffee')
            <path d="M10 2v2M14 2v2M17 8h1a4 4 0 1 1 0 8h-1" />
            <path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4z" />
            @break
        @case('bakeries')
            <path d="M12 3c-2.5 2-4 4.2-4 6.5A4 4 0 0 0 12 14a4 4 0 0 0 4-4.5C16 7.2 14.5 5 12 3Z" />
            <path d="M4 21v-5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v5" />
            <path d="M2 21h20" />
            @break
        @case('salons-barbershops')
            <circle cx="6" cy="6" r="3" />
            <circle cx="6" cy="18" r="3" />
            <path d="M20 4 8.12 15.88M14.47 14.48 20 20M8.12 8.12 12 12" />
            @break
        @case('gyms-fitness')
            <path d="M6.5 6.5 17.5 17.5" />
            <path d="m21 21-1.9-1.9M3.6 4.9 5.5 6.8M18.5 17.5l2.5 2.5M3 3l2.5 2.5" />
            <path d="M9.4 5.5 5.5 9.4a1 1 0 0 0 0 1.4L12.6 18a1 1 0 0 0 1.4 0l3.9-3.9a1 1 0 0 0 0-1.4L11.4 5.5a1 1 0 0 0-1.4 0Z" />
            @break
        @case('hotels')
            <path d="M2 22V4a1 1 0 0 1 1-1h9a1 1 0 0 1 1 1v18" />
            <path d="M13 8h7a1 1 0 0 1 1 1v13" />
            <path d="M6 8h.01M6 12h.01M6 16h.01M17 12h.01M17 16h.01" />
            <path d="M2 22h20" />
            @break
        @case('clinics-doctors')
            <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.29 1.51 4.04 3 5.5l7 7Z" />
            <path d="M12 8v4m-2-2h4" />
            @break
        @case('dentists')
            <path d="M8 2c1.5 0 2 1 4 1s2.5-1 4-1c2.5 0 4 2 4 5.5 0 4.5-2 6.5-2.5 10.5-.2 1.6-1 3-2.5 3-2 0-2-4-3-4s-1 4-3 4c-1.5 0-2.3-1.4-2.5-3C6 14 4 12 4 7.5 4 4 5.5 2 8 2Z" />
            @break
        @case('auto-repair')
            <path d="m14.7 6.3 1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76a1 1 0 0 0-.01 1.42Z" />
            @break
        @case('clothing-fashion')
            <path d="M12 2a3 3 0 0 0-3 3H7L2 9l3 3v9h14v-9l3-3-5-4h-2a3 3 0 0 0-3-3Z" />
            @break
        @default
            <path d="M7 3h5a2 2 0 0 1 1.4.6l7 7a2 2 0 0 1 0 2.8l-5.6 5.6a2 2 0 0 1-2.8 0l-7-7A2 2 0 0 1 3 12V7a4 4 0 0 1 4-4z" />
            <path d="M7 7h.01" />
    @endswitch
</svg>
