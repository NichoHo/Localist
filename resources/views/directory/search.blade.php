<x-public-layout :title="($q ? 'Search: '.$q : 'Search').' | Localist'" :noindex="true">
    <div class="mx-auto max-w-[76rem] px-4 py-10 sm:px-6 sm:py-12">
        <h1 class="font-display text-3xl font-bold text-ink sm:text-4xl">Search</h1>

        <form action="{{ route('search') }}" method="get" class="mt-6 flex max-w-xl flex-col gap-2 sm:flex-row">
            <div class="relative flex-1">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="pointer-events-none absolute left-4 top-1/2 size-5 -translate-y-1/2 text-ink-subtle" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m1.35-5.4a6.75 6.75 0 1 1-13.5 0 6.75 6.75 0 0 1 13.5 0Z" />
                </svg>
                <label for="search-q" class="sr-only">Search businesses</label>
                <input id="search-q" type="search" name="q" value="{{ $q }}" placeholder="Search businesses…" class="field py-3 pl-12">
            </div>
            <button type="submit" class="btn btn-primary shrink-0 px-6 py-3">Search</button>
        </form>

        <p class="mt-6 text-ink-muted"><span class="font-medium text-ink">{{ number_format($businesses->total()) }}</span> results{{ $q ? ' for "'.$q.'"' : '' }}</p>

        @if ($businesses->isEmpty())
            <div class="surface-card mt-8 flex flex-col items-center gap-4 px-6 py-16 text-center">
                <span class="grid size-14 place-items-center rounded-2xl bg-sunken text-ink-subtle">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" class="size-7" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m1.35-5.4a6.75 6.75 0 1 1-13.5 0 6.75 6.75 0 0 1 13.5 0Z" />
                    </svg>
                </span>
                <div>
                    <p class="font-display text-lg font-bold text-ink">No businesses found{{ $q ? ' for “'.$q.'”' : '' }}</p>
                    <p class="mt-1 text-ink-muted">Try a different search term, or browse by category from the home page.</p>
                </div>
                <a href="{{ route('home') }}" class="btn btn-ghost">Back to home</a>
            </div>
        @else
            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($businesses as $business)
                    <x-business-card :business="$business" />
                @endforeach
            </div>

            <div class="mt-10">{{ $businesses->links() }}</div>
        @endif
    </div>
</x-public-layout>
