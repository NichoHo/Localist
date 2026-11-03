<x-public-layout :title="'Local services in '.$city->name.' | Localist'" :description="'Browse trusted local service businesses in '.$city->name.', '.$city->region.' by category.'">
    <div class="mx-auto max-w-[76rem] px-4 py-6 sm:px-6 lg:py-12">
        <x-breadcrumbs :items="[['Home', route('home')], [$city->name, null]]" />

        <div class="mt-5">
            <p class="text-sm font-medium text-ink-subtle">{{ $city->region }}</p>
            <h1 class="mt-1 font-display text-3xl font-bold tracking-[-0.03em] text-ink sm:text-4xl lg:text-5xl">Local services in {{ $city->name }}</h1>
        </div>

        {{-- Featured first, together, ahead of the category browse --}}
        @if ($featured->isNotEmpty())
            <div class="mt-8 lg:mt-10">
                <h2 class="font-display text-xl font-bold tracking-tight text-ink lg:text-2xl">Featured in {{ $city->name }}</h2>
                <div class="business-list no-tint mt-5">
                    @foreach ($featured as $business)
                        <x-business-card :business="$business" />
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mt-12 lg:mt-14">
            <h2 class="font-display text-xl font-bold tracking-tight text-ink lg:text-2xl">Browse by category</h2>
            <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 lg:gap-4">
                @foreach ($categories as $category)
                    <x-category-tile :category="$category" :href="route('city.category', [$city, $category])" :meta="number_format($category->businesses_count).' in '.$city->name" />
                @endforeach
            </div>
        </div>
    </div>
</x-public-layout>
