<x-public-layout>
    @php
        $businessCount = cache()->remember('home.business_count', 3600, fn () => \App\Models\Business::published()->count());
    @endphp

    {{-- Hero: search is the CTA --}}
    <section class="relative isolate overflow-hidden">
        <img src="https://images.unsplash.com/photo-1556740738-b6a63e27c4df?w=1920&q=68&auto=format&fit=crop"
            alt="" aria-hidden="true" fetchpriority="high"
            class="absolute inset-0 -z-10 size-full object-cover object-center">
        <div class="absolute inset-0 -z-10 bg-gradient-to-br from-[#0b5d57]/95 via-[#0f766e]/90 to-[#0f766e]/60"></div>

        <div class="mx-auto max-w-[76rem] px-4 py-20 sm:px-6 sm:py-28 lg:py-32">
            <div class="max-w-2xl">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-white/75">Malaysia&rsquo;s local services directory</p>
                <h1 class="mt-3 font-display text-4xl font-bold leading-[1.05] text-white sm:text-5xl lg:text-6xl">Find trusted local services near you</h1>
                <p class="mt-5 max-w-xl text-lg leading-relaxed text-white/85">Compare {{ number_format($businessCount) }} verified plumbers, electricians, cleaners, tutors and more across {{ $cityCount }} Malaysian cities.</p>

                {{-- Signature: glassy search command bar (submit on Enter) --}}
                <form action="{{ route('search') }}" method="get" class="mt-8 flex items-center gap-3 rounded-2xl bg-surface p-2.5 pl-4 shadow-lift ring-1 ring-black/5">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="pointer-events-none size-5 shrink-0 text-ink-subtle" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m1.35-5.4a6.75 6.75 0 1 1-13.5 0 6.75 6.75 0 0 1 13.5 0Z" />
                    </svg>
                    <label for="hero-search" class="sr-only">What do you need help with?</label>
                    <input id="hero-search" type="search" name="q" placeholder="What do you need help with?"
                        class="min-w-0 flex-1 border-0 bg-transparent py-2.5 text-base text-ink placeholder:text-ink-subtle focus:ring-0">
                    <span class="hidden shrink-0 items-center gap-1 rounded-lg border border-line bg-sunken px-2.5 py-1.5 text-xs font-medium text-ink-subtle sm:inline-flex" aria-hidden="true">Enter &crarr;</span>
                </form>

                <div class="mt-5 flex flex-wrap items-center gap-2">
                    <span class="text-sm text-white/70">Popular:</span>
                    @foreach ($categories->take(6) as $category)
                        <a href="{{ route('category', $category) }}" class="rounded-full bg-white/10 px-3 py-1 text-sm font-medium text-white/90 ring-1 ring-inset ring-white/20 transition hover:bg-white/20">{{ $category->name }}</a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- Trust strip --}}
    <section class="border-b border-line bg-canvas">
        <div class="mx-auto grid max-w-[76rem] grid-cols-1 divide-y divide-line px-4 sm:grid-cols-3 sm:divide-x sm:divide-y-0 sm:px-6">
            @php
                $trust = [
                    ['M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z|m9 12 2 2 4-4', number_format($businessCount).'+ verified businesses', 'Every listing reviewed before it goes live'],
                    ['M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z|M12 8v0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z', $cityCount.' cities nationwide', 'From Kuala Lumpur to Penang and beyond'],
                    ['M7.9 20A9 9 0 1 0 4 16.1L2 22Z', 'Free to browse &amp; contact', 'No fees to find and message a business'],
                ];
            @endphp
            @foreach ($trust as [$paths, $title, $desc])
                <div class="flex items-start gap-3.5 py-6 sm:px-6">
                    <span class="mt-0.5 grid size-10 shrink-0 place-items-center rounded-xl bg-brand-soft text-brand ring-1 ring-inset ring-brand-line">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" class="size-5" aria-hidden="true">
                            @foreach (explode('|', $paths) as $d)<path d="{{ $d }}" />@endforeach
                        </svg>
                    </span>
                    <div>
                        <p class="font-semibold text-ink">{!! $title !!}</p>
                        <p class="mt-0.5 text-sm text-ink-muted">{{ $desc }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Browse by category --}}
    <section class="mx-auto max-w-[76rem] px-4 py-16 sm:px-6 sm:py-20">
        <div>
            <p class="eyebrow">Explore</p>
            <h2 class="mt-2 font-display text-2xl font-bold text-ink sm:text-3xl">Browse by category</h2>
        </div>
        <div class="mt-8 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($categories as $category)
                <x-category-tile :category="$category" :href="route('category', $category)" :meta="number_format($category->businesses_count).' listings'" />
            @endforeach
        </div>
    </section>

    {{-- Featured businesses --}}
    @if ($featured->isNotEmpty())
        <section class="border-y border-line bg-canvas-2">
            <div class="mx-auto max-w-[76rem] px-4 py-16 sm:px-6 sm:py-20">
                <div>
                    <p class="eyebrow">Handpicked</p>
                    <h2 class="mt-2 font-display text-2xl font-bold text-ink sm:text-3xl">Featured businesses</h2>
                </div>
                <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($featured as $business)
                        <x-business-card :business="$business" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Popular cities --}}
    <section class="mx-auto max-w-[76rem] px-4 py-16 sm:px-6 sm:py-20">
        <div>
            <p class="eyebrow">By location</p>
            <h2 class="mt-2 font-display text-2xl font-bold text-ink sm:text-3xl">Popular cities</h2>
        </div>
        <div class="mt-8 flex flex-wrap gap-2.5">
            @foreach ($cities as $city)
                <a href="{{ route('city', $city) }}" class="chip">
                    {{ $city->name }}
                    <span class="text-ink-subtle">{{ number_format($city->businesses_count) }}</span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- CTA: for businesses --}}
    <section class="mx-auto max-w-[76rem] px-4 pb-8 sm:px-6">
        <div class="relative isolate overflow-hidden rounded-3xl bg-gradient-to-br from-[#0b5d57] to-[#0f766e] px-6 py-14 text-center sm:px-12 sm:py-16">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-white/70">For business owners</p>
            <h2 class="mx-auto mt-3 max-w-2xl font-display text-2xl font-bold text-white sm:text-3xl">Own a local business? Get found by customers.</h2>
            <p class="mx-auto mt-4 max-w-xl text-white/80">Claim your free listing, manage it in minutes and reach people searching for exactly what you offer.</p>
            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <a href="{{ route('register') }}" class="btn bg-white px-6 py-3 text-base text-[#0b5d57] shadow-card hover:bg-white/90">List your business</a>
                <a href="{{ route('login') }}" class="btn border border-white/25 px-6 py-3 text-base text-white hover:bg-white/10">Owner login</a>
            </div>
        </div>
    </section>
</x-public-layout>
