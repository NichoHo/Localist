@props(['business', 'hideCategory' => false])

{{-- A row, not a card: a trade icon anchors identity on the left, a compact
     icon action sits on the right. Status sits inline with category/city,
     where a reader is already looking for it. Featured rows get a solid
     accent icon swatch instead of a neutral one — this is the paid tier, it
     should visibly earn that on the one element that's already meant to
     stand out (the accent color is reserved for exactly this elsewhere in
     the system), not just say so in a small tag. --}}
{{-- Rows separate via the parent `.business-list`'s `divide-y`, not a
     self-drawn border, so the last row in a list never dangles an extra
     rule beneath it. --}}
<div class="grid grid-cols-[1fr_auto] items-center gap-x-4 gap-y-1 px-4 py-5 transition hover:bg-canvas-2">
    <a href="{{ route('business', $business) }}" class="group flex min-w-0 items-center gap-3.5">
        <span @class([
            'grid size-10 shrink-0 place-items-center rounded-md ring-1 ring-inset transition duration-150',
            'bg-accent text-on-accent ring-accent-line' => $business->isFeatured(),
            'bg-brand-soft text-brand ring-brand-line group-hover:bg-brand group-hover:text-on-brand group-hover:ring-brand' => ! $business->isFeatured(),
        ])>
            <x-category-icon :slug="$business->category->slug" class="size-5" />
        </span>

        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                <h3 class="truncate text-base font-bold text-ink group-hover:underline">{{ $business->name }}</h3>
                @if ($business->isFeatured())
                    <span class="featured-tag">Featured</span>
                @endif
            </div>
            <p class="mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-0.5 text-sm text-ink-muted">
                <span class="min-w-0 truncate">
                    @unless ($hideCategory)
                        {{ $business->category->name }} &middot;
                    @endunless
                    {{ $business->city->name }}
                </span>
                @if ($label = $business->openStatusLabel())
                    <span @class(['status-pill shrink-0', 'status-open' => $business->isOpenNow(), 'status-shut' => ! $business->isOpenNow()])>{{ $label }}</span>
                @endif
            </p>
        </div>
    </a>

    @if ($business->phone)
        <a href="tel:{{ $business->phone }}" aria-label="Call {{ $business->name }}" class="row-action row-action-call">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-4.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.28 6.72 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.37c0-.52-.35-.97-.85-1.09l-4.42-1.11c-.44-.11-.9.06-1.17.42l-.97 1.29c-.28.38-.77.54-1.21.38a12.04 12.04 0 0 1-7.14-7.14c-.16-.44 0-.93.38-1.21l1.29-.97c.36-.27.53-.73.42-1.17L6.96 3.1a1.13 1.13 0 0 0-1.09-.85H4.5A2.25 2.25 0 0 0 2.25 4.5z" />
            </svg>
        </a>
    @else
        <a href="{{ route('business', $business) }}" aria-label="View {{ $business->name }}" class="row-action row-action-view">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-4.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
            </svg>
        </a>
    @endif
</div>
