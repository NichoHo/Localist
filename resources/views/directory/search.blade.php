<x-public-layout :title="($q ? 'Search: '.$q : 'Search').' | Localist'" :noindex="true">
    <div class="mx-auto max-w-[76rem] px-4 py-10 sm:px-6 sm:py-12">
        <h1 class="font-display text-3xl font-bold text-ink sm:text-4xl">Search</h1>

        <form action="{{ route('search') }}" method="get" class="mt-6 flex max-w-xl overflow-hidden rounded border-2 border-ink bg-surface">
            <label for="search-q" class="sr-only">Search businesses</label>
            <input id="search-q" type="search" name="q" value="{{ $q }}" placeholder="Search businesses…" class="min-w-0 flex-1 border-0 bg-transparent px-4 py-3 text-base text-ink placeholder:text-ink-subtle focus:outline-none focus:ring-0">
            <button type="submit" class="btn btn-accent shrink-0 rounded-none px-6 py-3">Search</button>
        </form>

        <p class="mt-6 text-ink-muted"><span class="font-mono font-medium text-ink">{{ number_format($businesses->total()) }}</span> results{{ $q ? ' for "'.$q.'"' : '' }}</p>

        @if ($businesses->isEmpty())
            <div class="surface-card mt-8 flex flex-col items-center gap-4 px-6 py-16 text-center">
                <div>
                    <p class="font-display text-lg font-bold text-ink">No businesses found{{ $q ? ' for “'.$q.'”' : '' }}</p>
                    <p class="mt-1 text-ink-muted">Try a different search term, or browse by category from the home page.</p>
                </div>
                <a href="{{ route('home') }}" class="btn btn-ghost">Back to home</a>
            </div>
        @else
            <div class="business-list mt-8">
                @foreach ($businesses as $business)
                    <x-business-card :business="$business" />
                @endforeach
            </div>

            <div class="mt-10">{{ $businesses->links() }}</div>
        @endif
    </div>
</x-public-layout>
