<x-public-layout :title="'Local services in '.$city->name.' | Localist'" :description="'Browse trusted local service businesses in '.$city->name.', '.$city->region.' by category.'">
    <div class="mx-auto max-w-[76rem] px-4 py-10 sm:px-6 sm:py-12">
        <x-breadcrumbs :items="[['Home', route('home')], [$city->name, null]]" />

        <div class="mt-5">
            <p class="text-sm font-medium text-ink-subtle">{{ $city->region }}</p>
            <h1 class="mt-1 font-display text-3xl font-bold text-ink sm:text-4xl">Local services in {{ $city->name }}</h1>
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
                <div class="business-list mt-5">
                    @foreach ($featured as $business)
                        <x-business-card :business="$business" />
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-public-layout>
