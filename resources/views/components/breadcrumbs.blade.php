@props(['items' => []])
{{-- $items: array of [label, url]. The last item is rendered as the current page. --}}

<nav aria-label="Breadcrumb" {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-1.5 text-sm text-ink-muted']) }}>
    @foreach ($items as [$label, $url])
        @if ($loop->last || ! $url)
            <span class="font-medium text-ink" aria-current="page">{{ $label }}</span>
        @else
            <a href="{{ $url }}" class="transition hover:text-brand">{{ $label }}</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-3.5 shrink-0 text-ink-subtle" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
            </svg>
        @endif
    @endforeach
</nav>
