@props(['name', 'class' => 'size-5'])

{{-- Small outline icon set (24px grid, 2px stroke) shared by the header, tab bars and listing actions. --}}
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}" aria-hidden="true">
    @switch($name)
        @case('home')
            <path d="m3 10.5 9-7.5 9 7.5V20a1 1 0 0 1-1 1h-5v-6h-6v6H4a1 1 0 0 1-1-1z" />
            @break
        @case('search')
            <circle cx="11" cy="11" r="7" /><path d="m20 20-3.5-3.5" />
            @break
        @case('plus')
            <path d="M12 5v14M5 12h14" />
            @break
        @case('user')
            <circle cx="12" cy="8" r="4" /><path d="M4 21a8 8 0 0 1 16 0" />
            @break
        @case('phone')
            <path d="M2.25 6.75c0 8.28 6.72 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.37c0-.52-.35-.97-.85-1.09l-4.42-1.11c-.44-.11-.9.06-1.17.42l-.97 1.29c-.28.38-.77.54-1.21.38a12.04 12.04 0 0 1-7.14-7.14c-.16-.44 0-.93.38-1.21l1.29-.97c.36-.27.53-.73.42-1.17L6.96 3.1a1.13 1.13 0 0 0-1.09-.85H4.5A2.25 2.25 0 0 0 2.25 4.5z" />
            @break
        @case('pin')
            <path d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
            @break
        @case('navigation')
            <path d="m3 11 18-8-8 18-2-8z" />
            @break
        @case('message')
            <path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1-4.6A8 8 0 1 1 21 12z" />
            @break
        @case('globe')
            <circle cx="12" cy="12" r="9" /><path d="M3.5 9h17M3.5 15h17M12 3a13 13 0 0 1 0 18 13 13 0 0 1 0-18Z" />
            @break
        @case('chevron-right')
            <path d="m9 5 7 7-7 7" />
            @break
        @case('chevron-left')
            <path d="m15 5-7 7 7 7" />
            @break
        @case('chevron-down')
            <path d="m5 9 7 7 7-7" />
            @break
        @case('arrow-up-right')
            <path d="M7 17 17 7M8 7h9v9" />
            @break
        @case('check')
            <path d="m4.5 12.75 6 6 9-13.5" />
            @break
        @case('dashboard')
            <rect x="3" y="3" width="7" height="9" rx="1.5" /><rect x="14" y="3" width="7" height="5" rx="1.5" /><rect x="14" y="12" width="7" height="9" rx="1.5" /><rect x="3" y="16" width="7" height="5" rx="1.5" />
            @break
        @case('pencil')
            <path d="M16.9 3.6a2 2 0 0 1 2.8 2.8L8 18.1 3.5 19.5 4.9 15z" />
            @break
        @case('inbox')
            <path d="M3 13h5l1.5 3h5L16 13h5" /><path d="M5.5 5h13L21 13v6a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1v-6z" />
            @break
        @case('card')
            <rect x="2.5" y="5" width="19" height="14" rx="2.5" /><path d="M2.5 10h19" />
            @break
        @case('star')
            <path d="M12 3l2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1-4.4-4.3 6.1-.9z" fill="currentColor" stroke="none" />
            @break
        @case('shield')
            <path d="M12 3 4 6v6c0 5 3.4 8.2 8 9 4.6-.8 8-4 8-9V6z" /><path d="m9 12 2.2 2.2L15.5 10" />
            @break
    @endswitch
</svg>
