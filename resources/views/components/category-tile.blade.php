@props(['category', 'href', 'meta'])

<a href="{{ $href }}" class="group surface-card hover-lift flex items-center gap-4 p-4 sm:p-5">
    <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-brand-soft text-brand ring-1 ring-inset ring-brand-line transition duration-200 group-hover:bg-brand group-hover:text-on-brand group-hover:ring-brand">
        <x-category-icon :slug="$category->slug" class="size-[1.35rem]" />
    </span>
    <div class="min-w-0 flex-1">
        <p class="truncate font-semibold text-ink transition group-hover:text-brand">{{ $category->name }}</p>
        <p class="mt-0.5 truncate text-sm text-ink-muted">{{ $meta }}</p>
    </div>
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-4 shrink-0 text-ink-subtle transition duration-200 group-hover:translate-x-0.5 group-hover:text-brand" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
    </svg>
</a>
