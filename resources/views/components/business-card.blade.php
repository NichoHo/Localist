@props(['business', 'hideCategory' => false])

{{-- One markup, two layouts (see .business-list in app.css).
     Below lg: a list row. An icon tile, the name, a meta line and the open status,
     with a round call button on the right.
     lg and up: a card. The icon tile and featured label across the top, the name and
     meta under them, then a footer with the status and a Call button.
     Featured listings carry only a quiet label; the list order (featured first) does
     the work. Rows separate through the parent's divide-y. --}}
@php
    $label = $business->openStatusLabel();
    $open = $business->isOpenNow();
@endphp
<div class="biz">
    <a href="{{ route('business', $business) }}" class="group flex min-w-0 flex-1 items-center gap-3.5 lg:flex-none lg:flex-col lg:items-stretch lg:gap-4">
        <div class="flex items-start justify-between gap-3">
            <span class="icon-tile size-12 lg:size-11"><x-category-icon :slug="$business->category->slug" class="size-5" /></span>
            @if ($business->isFeatured())
                <span class="featured-tag hidden lg:inline-flex">Featured</span>
            @endif
        </div>

        <div class="min-w-0 flex-1 lg:flex-none">
            <div class="flex items-center gap-2">
                <h3 class="truncate text-base font-semibold leading-snug text-ink lg:whitespace-normal lg:text-lg">{{ $business->name }}</h3>
                @if ($business->isFeatured())
                    <span class="featured-tag shrink-0 lg:hidden">Featured</span>
                @endif
            </div>
            <p class="mt-0.5 truncate text-sm text-ink-muted lg:whitespace-normal">
                @unless ($hideCategory)
                    {{ $business->category->name }} &middot;
                @endunless
                {{ $business->city->name }}
            </p>
            @if ($label)
                <span @class(['status-pill mt-1 lg:hidden', 'status-open' => $open, 'status-shut' => ! $open])>{{ $label }}</span>
            @endif
        </div>
    </a>

    <div class="flex shrink-0 items-center lg:justify-between lg:gap-3 lg:border-t lg:border-line lg:pt-4">
        @if ($label)
            <span @class(['status-pill hidden lg:inline-flex', 'status-open' => $open, 'status-shut' => ! $open])>{{ $label }}</span>
        @else
            <span class="hidden lg:block"></span>
        @endif

        @if ($business->phone)
            <a href="tel:{{ $business->phone }}" aria-label="Call {{ $business->name }}" class="row-action row-action-call lg:size-auto lg:h-10 lg:gap-2 lg:px-4">
                <x-icon name="phone" class="size-[18px]" />
                <span class="hidden text-sm font-semibold lg:inline">Call</span>
            </a>
        @else
            <a href="{{ route('business', $business) }}" aria-label="View {{ $business->name }}" class="row-action row-action-view lg:size-auto lg:h-10 lg:gap-2 lg:px-4">
                <x-icon name="chevron-right" class="size-[18px]" />
                <span class="hidden text-sm font-semibold lg:inline">View</span>
            </a>
        @endif
    </div>
</div>
