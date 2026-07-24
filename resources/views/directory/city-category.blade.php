<x-public-layout :title="$category->name.' in '.$city->name.' | Localist'" :description="'Compare '.number_format($businesses->total()).' trusted '.strtolower($category->name).' in '.$city->name.', '.$city->region.'.'">
    <x-json-ld :data="\App\Services\Seo::itemList($businesses->getCollection(), $category->name.' in '.$city->name)" />
    <x-json-ld :data="\App\Services\Seo::breadcrumbs([
        ['Home', route('home')],
        [$city->name, route('city', $city)],
        [$category->name, route('city.category', [$city, $category])],
    ])" />

    <div class="mx-auto max-w-[76rem] px-4 py-10 sm:px-6 sm:py-12">
        <x-breadcrumbs :items="[['Home', route('home')], [$city->name, route('city', $city)], [$category->name, null]]" />

        <div class="mt-5 flex items-start gap-4">
            <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-brand-soft text-brand ring-1 ring-inset ring-brand-line">
                <x-category-icon :slug="$category->slug" class="size-7" />
            </span>
            <div>
                <h1 class="font-display text-3xl font-bold text-ink sm:text-4xl">{{ $category->name }} in {{ $city->name }}</h1>
                <p class="mt-1 text-ink-muted"><span class="font-medium text-ink">{{ number_format($businesses->total()) }}</span> businesses · {{ $city->region }}</p>
            </div>
        </div>

        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($businesses as $business)
                <x-business-card :business="$business" />
            @endforeach
        </div>

        <div class="mt-10">{{ $businesses->links() }}</div>

        @if ($nearbyCities->isNotEmpty())
            <div class="mt-16 border-t border-line-subtle pt-8">
                <h2 class="font-display text-lg font-bold text-ink">{{ $category->name }} in nearby cities</h2>
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach ($nearbyCities as $nearby)
                        <a href="{{ route('city.category', [$nearby, $category]) }}" class="chip">{{ $nearby->name }}</a>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($relatedCategories->isNotEmpty())
            <div class="mt-10">
                <h2 class="font-display text-lg font-bold text-ink">Other services in {{ $city->name }}</h2>
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach ($relatedCategories as $related)
                        <a href="{{ route('city.category', [$city, $related]) }}" class="chip">{{ $related->name }}</a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-public-layout>
