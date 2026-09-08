@props(['slug' => '', 'class' => 'size-6'])

<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}" aria-hidden="true">
    @switch($slug)
        @case('plumbers')
            <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76Z" />
            @break
        @case('electricians')
            <path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z" />
            @break
        @case('cleaners')
            <path d="M9.94 14.06A2 2 0 0 0 8.5 12.6l-4.9-1.26a.4.4 0 0 1 0-.78L8.5 9.3a2 2 0 0 0 1.44-1.44l1.26-4.9a.4.4 0 0 1 .78 0l1.26 4.9A2 2 0 0 0 15.5 9.3l4.9 1.26a.4.4 0 0 1 0 .78L15.5 12.6a2 2 0 0 0-1.44 1.46l-1.26 4.9a.4.4 0 0 1-.78 0z" />
            <path d="M18 5.5h.01M19.5 15.5h.01" />
            @break
        @case('tutors')
            <path d="M21.42 10.92a1 1 0 0 0-.02-1.84L12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.84l8.57 3.9a2 2 0 0 0 1.66 0z" />
            <path d="M22 10v6M6 12.5V16a6 3 0 0 0 12 0v-3.5" />
            @break
        @case('movers')
            <path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h1" />
            <path d="M14 9h3.5a1 1 0 0 1 .78.38l3.22 4a1 1 0 0 1 .22.62V17a1 1 0 0 1-1 1h-1" />
            <path d="M9 18h5" />
            <circle cx="7" cy="18" r="2" />
            <circle cx="17" cy="18" r="2" />
            @break
        @case('aircon-servicing')
            <path d="M12.8 19.6A2 2 0 1 0 14 16H2" />
            <path d="M17.5 8a2.5 2.5 0 1 1 2 4H2" />
            <path d="M9.8 4.4A2 2 0 1 1 11 8H2" />
            @break
        @case('pest-control')
            <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z" />
            <path d="m9 12 2 2 4-4" />
            @break
        @case('handyman')
            <path d="m13 3 8 8-2.5 2.5-8-8z" />
            <path d="M8.5 8.5 3 14a2.12 2.12 0 1 0 3 3l5.5-5.5" />
            @break
        @case('landscapers')
            <path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z" />
            <path d="M2 21c0-3 1.85-5.36 5.08-6" />
            @break
        @case('locksmiths')
            <path d="M2.59 17.41A2 2 0 0 0 2 18.83V21a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1v-1a1 1 0 0 1 1-1h1a1 1 0 0 0 1-1v-1a1 1 0 0 1 1-1h.17a2 2 0 0 0 1.42-.59l.81-.81a6.5 6.5 0 1 0-4-4z" />
            <path d="M16.5 7.5h.01" />
            @break
        @case('painters')
            <path d="M17 3a2 2 0 0 1 2 2v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z" />
            <path d="M10 21v-4a2 2 0 0 1 2-2h4a2 2 0 0 0 2-2V9" />
            @break
        @case('roofers')
            <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
            <path d="M9 22V12h6v10" />
            @break
        @default
            <path d="M7 3h5a2 2 0 0 1 1.4.6l7 7a2 2 0 0 1 0 2.8l-5.6 5.6a2 2 0 0 1-2.8 0l-7-7A2 2 0 0 1 3 12V7a4 4 0 0 1 4-4z" />
            <path d="M7 7h.01" />
    @endswitch
</svg>
