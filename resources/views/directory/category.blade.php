<x-public-layout :title="$category->name.' | Localist'" :description="'Find trusted '.strtolower($category->name).' near you. Compare '.number_format($businesses->total()).' local businesses on Localist.'">
    <x-json-ld :data="\App\Services\Seo::itemList($businesses->getCollection(), $category->name.' on Localist')" />

    <div class="mx-auto max-w-[76rem] px-4 py-10 sm:px-6 sm:py-12">
        <x-breadcrumbs :items="[['Home', route('home')], [$category->name, null]]" />

        <div class="mt-5 flex items-start gap-4">
            <span class="grid size-12 shrink-0 place-items-center rounded-md bg-brand-soft text-brand ring-1 ring-inset ring-brand-line">
                <x-category-icon :slug="$category->slug" class="size-6" />
            </span>
            <div>
                <h1 class="font-display text-3xl font-bold text-ink sm:text-4xl">{{ $category->name }}</h1>
                <p class="mt-1 text-ink-muted"><span class="font-mono font-medium text-ink">{{ number_format($businesses->total()) }}</span> businesses across Indonesia</p>
            </div>
        </div>

        @if ($cities->isNotEmpty())
            {{-- City filter — collapses to one row; "show more" is a CSS-only peer toggle (public pages have no Alpine) --}}
            <div class="mt-8 flex flex-wrap gap-2">
                <input type="checkbox" id="more-cities" class="peer sr-only" aria-label="Show all cities">
                <a href="{{ route('category', $category) }}" @class(['chip', 'chip-active' => ! request('city')])>All cities</a>
                @foreach ($cities as $city)
                    <a href="{{ route('city.category', [$city, $category]) }}"
                        @class([
                            'chip',
                            'chip-active' => request('city') === $city->slug,
                            'hidden peer-checked:inline-flex' => $loop->index >= 9 && request('city') !== $city->slug,
                        ])>{{ $city->name }}</a>
                @endforeach
                @if ($cities->count() > 9)
                    <label for="more-cities" class="chip cursor-pointer select-none peer-checked:hidden">+{{ $cities->count() - 9 }} more</label>
                    <label for="more-cities" class="chip hidden cursor-pointer select-none peer-checked:inline-flex">Show fewer</label>
                @endif
            </div>
        @endif

        <div class="business-list mt-8">
            @foreach ($businesses as $business)
                <x-business-card :business="$business" :hide-category="true" />
            @endforeach
        </div>

        <div class="mt-10">{{ $businesses->links() }}</div>
    </div>
</x-public-layout>
