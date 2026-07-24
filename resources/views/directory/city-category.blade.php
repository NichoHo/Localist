<x-public-layout :title="$category->name.' in '.$city->name.' | Localist'" :description="'Compare '.number_format($businesses->total()).' trusted '.strtolower($category->name).' in '.$city->name.', '.$city->region.'.'">
    <x-json-ld :data="\App\Services\Seo::itemList($businesses->getCollection(), $category->name.' in '.$city->name)" />
    <x-json-ld :data="\App\Services\Seo::breadcrumbs([
        ['Home', route('home')],
        [$city->name, route('city', $city)],
        [$category->name, route('city.category', [$city, $category])],
    ])" />
    <div class="mx-auto max-w-[75rem] px-4 py-10">
        <nav class="text-sm text-gray-500 dark:text-gray-400">
            <a href="{{ route('home') }}" class="hover:text-teal-700 dark:hover:text-teal-400">Home</a> /
            <a href="{{ route('city', $city) }}" class="hover:text-teal-700 dark:hover:text-teal-400">{{ $city->name }}</a> /
            {{ $category->name }}
        </nav>
        <h1 class="mt-2 text-3xl font-bold">{{ $category->name }} in {{ $city->name }}</h1>
        <p class="mt-1 text-gray-500 dark:text-gray-400">{{ number_format($businesses->total()) }} businesses · {{ $city->region }}</p>

        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($businesses as $business)
                <x-business-card :business="$business" />
            @endforeach
        </div>

        <div class="mt-8">{{ $businesses->links() }}</div>

        @if ($nearbyCities->isNotEmpty())
            <h2 class="mt-12 text-lg font-semibold">{{ $category->name }} in nearby cities</h2>
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach ($nearbyCities as $nearby)
                    <a href="{{ route('city.category', [$nearby, $category]) }}"
                        class="rounded-full border border-gray-200 dark:border-gray-800 px-3 py-1.5 text-sm font-medium hover:border-teal-600 hover:text-teal-700 dark:hover:text-teal-400">{{ $nearby->name }}</a>
                @endforeach
            </div>
        @endif

        @if ($relatedCategories->isNotEmpty())
            <h2 class="mt-8 text-lg font-semibold">Other services in {{ $city->name }}</h2>
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach ($relatedCategories as $related)
                    <a href="{{ route('city.category', [$city, $related]) }}"
                        class="rounded-full border border-gray-200 dark:border-gray-800 px-3 py-1.5 text-sm font-medium hover:border-teal-600 hover:text-teal-700 dark:hover:text-teal-400">{{ $related->name }}</a>
                @endforeach
            </div>
        @endif
    </div>
</x-public-layout>
