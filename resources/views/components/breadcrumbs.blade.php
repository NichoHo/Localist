@props(['items' => []])
{{-- $items: array of [label, url]. The last item is rendered as the current page. --}}

<nav aria-label="Breadcrumb" {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-1.5 text-sm text-ink-muted']) }}>
    @foreach ($items as [$label, $url])
        @if ($loop->last || ! $url)
            <span class="font-medium text-ink" aria-current="page">{{ $label }}</span>
        @else
            <a href="{{ $url }}" class="transition-colors hover:text-ink">{{ $label }}</a>
            <x-icon name="chevron-right" class="size-3.5 shrink-0 text-ink-subtle" />
        @endif
    @endforeach
</nav>
