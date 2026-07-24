<x-public-layout :title="'Local services in '.$city->name.' | Localist'" :description="'Browse trusted local service businesses in '.$city->name.', '.$city->region.' by category.'">
    <div class="mx-auto max-w-[76rem] px-4 py-10 sm:px-6 sm:py-12">
        <x-breadcrumbs :items="[['Home', route('home')], [$city->name, null]]" />

        <div class="mt-5">
            <p class="eyebrow inline-flex items-center gap-1.5">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="size-3.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                </svg>
                {{ $city->region }}
            </p>
            <h1 class="mt-2 font-display text-3xl font-bold text-ink sm:text-4xl">Local services in {{ $city->name }}</h1>
        </div>

        <div class="mt-10">
            <h2 class="font-display text-xl font-bold text-ink">Browse by category</h2>
            <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($categories as $category)
                    <x-category-tile :category="$category" :href="route('city.category', [$city, $category])" :meta="number_format($category->businesses_count).' in '.$city->name" />
                @endforeach
            </div>
        </div>

        @if ($featured->isNotEmpty())
            <div class="mt-14">
                <h2 class="font-display text-xl font-bold text-ink">Featured in {{ $city->name }}</h2>
                <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($featured as $business)
                        <x-business-card :business="$business" />
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-public-layout>
