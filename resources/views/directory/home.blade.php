<x-public-layout>
    @php
        $businessCount = cache()->remember('home.business_count', 3600, fn () => \App\Models\Business::published()->count());
        // Time-sensitive, so a short TTL rather than the hour-long cache above.
        // city_id must be selected too: isOpenNow() reads the business's city timezone.
        $openCount = cache()->remember('home.open_now_count', 300, fn () => \App\Models\Business::published()->with('city:id,timezone')->get(['id', 'city_id', 'hours'])->filter->isOpenNow()->count());
    @endphp

    {{-- Hero. Desktop: headline and search on the left, a live "open right now" panel on the right.
         Mobile: headline, a big search field and a scrolling row of categories. --}}
    <section class="mx-auto grid max-w-[76rem] items-center gap-10 px-4 pb-8 pt-8 sm:px-6 lg:grid-cols-[1.15fr_1fr] lg:gap-14 lg:pb-14 lg:pt-16">
        <div class="min-w-0">
            <span class="inline-flex items-center gap-2 rounded-full border border-line bg-surface px-3 py-1 text-[13px] font-medium text-ink-muted">
                @if ($openCount > 0)
                    <span class="size-2 rounded-full bg-success"></span>{{ number_format($openCount) }} places open right now
                @else
                    <span class="size-2 rounded-full bg-ink-subtle"></span>{{ number_format($businessCount) }} places listed
                @endif
            </span>
            <h1 class="mt-5 font-display text-[2.5rem] font-bold leading-[1.02] tracking-[-0.035em] text-ink sm:text-6xl lg:text-[4.5rem]">Find what's <mark class="rounded-xl bg-accent px-2 text-on-accent">open</mark> near you, right now.</h1>
            <p class="mt-5 max-w-lg text-base leading-relaxed text-ink-muted sm:text-lg">{{ number_format($businessCount) }} restaurants, cafes, salons, clinics and more across {{ $cityCount }} Malaysian cities. Free to search, free to call.</p>

            <form action="{{ route('search') }}" method="get" class="mt-7 flex max-w-xl items-center gap-2 rounded-2xl border border-line-strong bg-surface p-1.5 transition-colors focus-within:border-ink sm:mt-8 lg:rounded-full">
                <label for="hero-search" class="sr-only">What are you looking for?</label>
                <x-icon name="search" class="ml-3 size-5 shrink-0 text-ink-subtle" />
                <input id="hero-search" type="search" name="q" placeholder="Nasi lemak, dentist, hotel in Penang"
                    class="min-w-0 flex-1 border-0 bg-transparent px-1 py-3 text-base text-ink placeholder:text-ink-subtle focus:outline-none focus:ring-0 lg:py-2.5">
                <button type="submit" class="btn btn-primary shrink-0 px-6 py-3 lg:py-2.5">Search</button>
            </form>

            <div class="-mx-4 mt-5 flex gap-2 overflow-x-auto px-4 pb-1 [scrollbar-width:none] sm:mx-0 sm:px-0 lg:flex-wrap [&::-webkit-scrollbar]:hidden">
                @foreach ($categories->take(6) as $category)
                    <a href="{{ route('category', $category) }}" class="chip shrink-0">
                        <x-category-icon :slug="$category->slug" class="size-4" />
                        {{ $category->name }}
                    </a>
                @endforeach
            </div>
        </div>

        @if ($openNow->isNotEmpty())
            <div class="surface-card hidden overflow-hidden lg:block">
                <div class="flex items-start justify-between gap-4 px-6 pb-3 pt-6">
                    <div>
                        <h2 class="font-display text-lg font-bold text-ink">Open right now</h2>
                        <p class="mt-0.5 text-sm text-ink-muted">Featured listings first</p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-success-text"><span class="size-1.5 animate-pulse rounded-full bg-success"></span>Live</span>
                </div>
                <ul>
                    @foreach ($openNow as $business)
                        <li class="border-t border-line">
                            <a href="{{ route('business', $business) }}" class="flex items-center gap-3 px-6 py-3.5 transition-colors hover:bg-canvas-2">
                                <span class="icon-tile size-10"><x-category-icon :slug="$business->category->slug" class="size-[1.15rem]" /></span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate font-medium text-ink">{{ $business->name }}</span>
                                    <span class="block truncate text-sm text-ink-muted">{{ $business->category->name }} &middot; {{ $business->city->name }}</span>
                                </span>
                                @if ($business->isFeatured())
                                    <span class="featured-tag shrink-0">Featured</span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </section>

    {{-- Featured businesses come straight after the hero, together, never scattered through other lists --}}
    @if ($featured->isNotEmpty())
        <section class="mx-auto max-w-[76rem] px-4 py-8 sm:px-6 lg:py-12">
            <div class="mb-5 lg:mb-6">
                <h2 class="font-display text-2xl font-bold tracking-tight text-ink lg:text-3xl">Featured businesses</h2>
                <p class="mt-1 text-ink-muted">Paid placement, shown first.</p>
            </div>
            <div class="business-list">
                @foreach ($featured as $business)
                    <x-business-card :business="$business" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- Browse by category --}}
    <section class="mx-auto max-w-[76rem] px-4 py-8 sm:px-6 lg:py-12">
        <h2 class="font-display text-2xl font-bold tracking-tight text-ink lg:text-3xl">Browse by category</h2>
        <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:mt-6 lg:grid-cols-3 lg:gap-4">
            @foreach ($categories as $category)
                <x-category-tile :category="$category" :href="route('category', $category)" :meta="number_format($category->businesses_count).' listings'" />
            @endforeach
        </div>
    </section>

    {{-- Popular cities --}}
    <section class="mx-auto max-w-[76rem] px-4 py-8 sm:px-6 lg:py-12">
        <h2 class="font-display text-2xl font-bold tracking-tight text-ink lg:text-3xl">Browse by city</h2>
        <div class="mt-5 grid grid-cols-1 gap-x-10 sm:grid-cols-2 lg:mt-6 lg:grid-cols-3">
            @foreach ($cities as $city)
                <a href="{{ route('city', $city) }}" class="flex items-center justify-between border-t border-line py-3.5 text-ink transition-colors hover:text-ink-muted">
                    <span class="font-medium">{{ $city->name }}</span>
                    <span class="text-sm tabular-nums text-ink-muted">{{ number_format($city->businesses_count) }}</span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- CTA for businesses --}}
    <section class="mx-auto max-w-[76rem] px-4 pt-6 sm:px-6 lg:pt-10">
        <div class="grid gap-8 rounded-3xl bg-accent p-7 sm:p-10 lg:grid-cols-[1.2fr_1fr] lg:items-center lg:p-12">
            <div>
                <h2 class="font-display text-3xl font-bold leading-[1.05] tracking-tight text-on-accent sm:text-4xl">Own a local business? Get found by customers.</h2>
                <p class="mt-4 max-w-md text-on-accent/75">Claim your free listing, keep your hours right and answer enquiries from one dashboard.</p>
                <div class="mt-7 flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="btn btn-on-accent btn-lg focus-visible:ring-offset-accent">List your business</a>
                    <a href="{{ route('login') }}" class="btn btn-on-accent-ghost btn-lg focus-visible:ring-offset-accent">Owner login</a>
                </div>
            </div>
            <div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-1">
                @foreach ([['Free', 'Claim and edit your listing'], ['Featured', 'Shown first in your city and category'], ['Enquiries', 'Leads land in your inbox']] as [$t, $d])
                    <div class="rounded-2xl bg-on-accent/10 p-4">
                        <p class="font-display font-semibold text-on-accent">{{ $t }}</p>
                        <p class="text-sm text-on-accent/75">{{ $d }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</x-public-layout>
