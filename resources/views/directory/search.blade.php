<x-public-layout :title="($q ? 'Search: '.$q : 'Search').' | Localist'" :noindex="true">
    <div class="mx-auto max-w-[76rem] px-4 py-6 sm:px-6 lg:py-12">
        <h1 class="font-display text-3xl font-bold tracking-[-0.03em] text-ink sm:text-4xl lg:text-5xl">Search</h1>

        <form action="{{ route('search') }}" method="get" class="mt-6 flex max-w-xl items-center gap-2 rounded-2xl border border-line-strong bg-surface p-1.5 transition-colors focus-within:border-ink lg:rounded-full">
            <label for="search-q" class="sr-only">Search businesses</label>
            <input id="search-q" type="search" name="q" value="{{ $q }}" placeholder="Search businesses…" class="min-w-0 flex-1 border-0 bg-transparent px-3 py-2.5 text-base text-ink placeholder:text-ink-subtle focus:outline-none focus:ring-0">
            <button type="submit" class="btn btn-primary shrink-0 px-6 py-3 lg:py-2.5">Search</button>
        </form>

        <p class="mt-6 text-ink-muted"><span class="font-medium text-ink">{{ number_format($businesses->total()) }}</span> results{{ $q ? ' for "'.$q.'"' : '' }}</p>

        @if ($businesses->isEmpty())
            <div class="surface-card mt-8 flex flex-col items-center gap-4 px-6 py-16 text-center">
                <div>
                    <p class="font-display text-lg font-bold text-ink">No businesses found{{ $q ? ' for “'.$q.'”' : '' }}</p>
                    <p class="mt-1 text-ink-muted">Try a different search term, or browse by category from the home page.</p>
                </div>
                <a href="{{ route('home') }}" class="btn btn-ghost">Back to home</a>
            </div>
        @else
            <div class="business-list mt-6 lg:mt-8">
                @foreach ($businesses as $business)
                    <x-business-card :business="$business" />
                @endforeach
            </div>

            <div class="mt-10">{{ $businesses->links() }}</div>
        @endif
    </div>
</x-public-layout>
