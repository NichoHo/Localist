<x-public-layout>
    @php
        $businessCount = cache()->remember('home.business_count', 3600, fn () => \App\Models\Business::published()->count());
        // Time-sensitive, so a short TTL rather than the hour-long cache above.
        // city_id must be selected too: isOpenNow() reads the business's city timezone.
        $openCount = cache()->remember('home.open_now_count', 300, fn () => \App\Models\Business::published()->with('city:id,timezone')->get(['id', 'city_id', 'hours'])->filter->isOpenNow()->count());
    @endphp

    {{-- Hero: search is the CTA. No stock photo, no gradient — the headline
         and the findbar carry the section on their own. --}}
    <section class="mx-auto max-w-[76rem] px-4 pb-10 pt-14 sm:px-6 sm:pt-20">
        <div class="max-w-2xl">
            <h1 class="font-display text-4xl font-bold leading-[1.05] text-ink sm:text-5xl lg:text-6xl">Find what's <mark class="bg-accent px-1.5 text-on-accent">open</mark> right now.</h1>
            <p class="mt-5 max-w-xl text-lg leading-relaxed text-ink-muted">{{ number_format($businessCount) }} restaurants, cafes, salons, clinics and more across {{ $cityCount }} Indonesian cities. Free to search, free to call.</p>

            <form action="{{ route('search') }}" method="get" class="mt-8 flex max-w-xl flex-col overflow-hidden rounded border-2 border-ink bg-surface sm:flex-row">
                <label for="hero-search" class="sr-only">What are you looking for?</label>
                <input id="hero-search" type="search" name="q" placeholder="Nasi goreng near me, dentist open now, hotel in Bali"
                    class="min-w-0 flex-1 border-0 bg-transparent px-4 py-3.5 text-base text-ink placeholder:text-ink-subtle focus:outline-none focus:ring-0">
                <button type="submit" class="btn btn-accent rounded-none px-6 py-3.5 text-base sm:rounded-none">Search</button>
            </form>

            <div class="mt-5 flex flex-wrap items-center gap-2">
                <span class="text-sm text-ink-subtle">Try:</span>
                @foreach ($categories->take(6) as $category)
                    <a href="{{ route('category', $category) }}" class="rounded border border-line-strong px-3 py-1.5 text-sm font-medium text-ink transition hover:bg-canvas-2">{{ $category->name }}</a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Stat readout: tabular figures, the fact that matters ("open right
         now") given equal weight to the vanity numbers next to it. --}}
    <section class="border-y border-line bg-canvas">
        <div class="mx-auto max-w-[76rem] px-4 sm:px-6">
            <div class="grid grid-cols-2 divide-x divide-y divide-line sm:grid-cols-4 sm:divide-y-0">
                @foreach ([
                    [number_format($businessCount), 'Listings'],
                    [$cityCount, 'Cities'],
                    [$categories->count(), 'Categories'],
                    [number_format($openCount), 'Open right now'],
                ] as [$value, $label])
                    <div class="px-4 py-6 first:pl-0 sm:px-6">
                        <p class="font-mono text-2xl font-bold tabular-nums text-ink sm:text-3xl">{{ $value }}</p>
                        <p class="mt-1 text-sm text-ink-muted">{{ $label }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Browse by category --}}
    <section class="mx-auto max-w-[76rem] px-4 py-16 sm:px-6 sm:py-20">
        <h2 class="font-display text-2xl font-bold text-ink sm:text-3xl">Browse by category</h2>
        <div class="mt-8 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($categories as $category)
                <x-category-tile :category="$category" :href="route('category', $category)" :meta="number_format($category->businesses_count).' listings'" />
            @endforeach
        </div>
    </section>

    {{-- Featured businesses — a dense row list, not a padded card grid --}}
    @if ($featured->isNotEmpty())
        <section class="border-y border-line bg-canvas-2">
            <div class="mx-auto max-w-[76rem] px-4 py-16 sm:px-6 sm:py-20">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="font-display text-2xl font-bold text-ink sm:text-3xl">Featured businesses</h2>
                    <span class="featured-flag hidden sm:inline-flex">Paid placement</span>
                </div>
                <div class="business-list mt-6">
                    @foreach ($featured as $business)
                        <x-business-card :business="$business" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Popular cities --}}
    <section class="mx-auto max-w-[76rem] px-4 py-16 sm:px-6 sm:py-20">
        <h2 class="font-display text-2xl font-bold text-ink sm:text-3xl">Popular cities</h2>
        <div class="mt-8 flex flex-wrap gap-2.5">
            @foreach ($cities as $city)
                <a href="{{ route('city', $city) }}" class="chip">
                    {{ $city->name }}
                    <span class="text-ink-subtle">{{ number_format($city->businesses_count) }}</span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- CTA: for businesses. Solid yellow, the one other place on the page
         it appears besides Featured flags — reserved for exactly this. A
         real photo earns the other half of the panel instead of leaving it
         a flat color block; desaturated so it sits inside the ink/paper
         palette instead of fighting it with its own colors. --}}
    {{-- Photo: Inna Safa via Unsplash, free license, no attribution required. --}}
    <section class="mx-auto max-w-[76rem] px-4 sm:px-6">
        <div class="grid overflow-hidden rounded-md bg-accent sm:grid-cols-2">
            <div class="flex flex-col justify-center px-6 py-14 text-center sm:order-2 sm:px-12 sm:py-16 sm:text-left">
                <h2 class="font-display text-2xl font-bold text-on-accent sm:text-3xl">Own a local business? Get found by customers.</h2>
                <p class="mt-4 text-on-accent/75">Claim your free listing, manage it in minutes and reach people searching for exactly what you offer.</p>
                <div class="mt-8 flex flex-wrap justify-center gap-3 sm:justify-start">
                    <a href="{{ route('register') }}" class="btn btn-on-accent px-6 py-3 text-base font-bold uppercase tracking-wide focus-visible:ring-offset-accent">List your business</a>
                    <a href="{{ route('login') }}" class="btn btn-on-accent-ghost px-6 py-3 text-base font-bold uppercase tracking-wide focus-visible:ring-offset-accent">Owner login</a>
                </div>
            </div>
            <div class="min-h-56 sm:order-1">
                <img src="https://images.unsplash.com/photo-1708493449820-d8f0507bc29e?fm=jpg&amp;q=80&amp;w=1200&amp;h=800&amp;fit=crop&amp;auto=format"
                    alt="A woman preparing food at her small kitchen counter for a customer"
                    width="1200" height="800" loading="lazy"
                    class="h-full w-full object-cover grayscale contrast-125">
            </div>
        </div>
    </section>
</x-public-layout>
