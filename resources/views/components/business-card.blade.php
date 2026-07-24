@props(['business', 'hideCategory' => false])

<a href="{{ route('business', $business) }}"
    @class([
        'group surface-card hover-lift flex min-w-0 items-start gap-3 p-4 sm:p-5',
        'ring-1 ring-accent-line' => $business->isFeatured(),
    ])>
    <div class="min-w-0 flex-1">
        <div class="flex items-center gap-2">
            <h3 class="truncate font-semibold text-ink">{{ $business->name }}</h3>
            @if ($business->isFeatured())
                <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-accent-soft px-2 py-0.5 text-xs font-semibold text-accent-text ring-1 ring-inset ring-accent-line">
                    <svg viewBox="0 0 24 24" fill="currentColor" class="size-3" aria-hidden="true"><path d="M11.48 3.5a.56.56 0 0 1 1.04 0l2.02 4.87 5.26.42c.5.04.7.66.32.99l-4.01 3.43 1.22 5.13a.56.56 0 0 1-.84.6L12 16.9l-4.5 2.75a.56.56 0 0 1-.84-.6l1.22-5.13-4.01-3.43a.56.56 0 0 1 .32-.99l5.26-.42 2.02-4.87Z" /></svg>
                    Featured
                </span>
            @endif
        </div>

        @if ($hideCategory)
            <p class="mt-1.5 flex items-center gap-1.5 text-sm text-ink-muted">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="size-4 shrink-0 text-brand" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                </svg>
                <span class="truncate">{{ $business->city->name }}</span>
            </p>
            @if ($business->address)
                <p class="mt-0.5 truncate text-sm text-ink-subtle">{{ $business->address }}</p>
            @endif
        @else
            <p class="mt-1.5 flex items-center gap-1.5 text-sm text-ink-muted">
                <x-category-icon :slug="$business->category->slug" class="size-4 shrink-0 text-brand" />
                <span class="truncate">{{ $business->category->name }} · {{ $business->city->name }}</span>
            </p>
            @if ($business->address)
                <p class="mt-1 flex items-center gap-1.5 text-sm text-ink-subtle">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="size-3.5 shrink-0" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                    </svg>
                    <span class="truncate">{{ $business->address }}</span>
                </p>
            @endif
        @endif
    </div>

    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mt-0.5 hidden size-5 shrink-0 text-ink-subtle transition duration-200 group-hover:translate-x-0.5 group-hover:text-brand sm:block" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
    </svg>
</a>
