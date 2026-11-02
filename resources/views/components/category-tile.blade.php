@props(['category', 'href', 'meta'])

{{-- Hover only deepens the border a step. The icon, title and arrow stay put. --}}
<a href="{{ $href }}" class="surface-card hover-lift flex items-center gap-4 p-4 lg:p-5">
    <span class="icon-tile size-12"><x-category-icon :slug="$category->slug" class="size-[1.35rem]" /></span>
    <div class="min-w-0 flex-1">
        <p class="truncate text-base font-semibold text-ink">{{ $category->name }}</p>
        <p class="mt-0.5 truncate text-sm text-ink-muted">{{ $meta }}</p>
    </div>
    <x-icon name="chevron-right" class="size-4 shrink-0 text-ink-subtle" />
</a>
