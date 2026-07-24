<x-public-layout :title="$category->name.' | Localist'" :description="'Find trusted '.strtolower($category->name).' near you. Compare '.number_format($businesses->total()).' local businesses on Localist.'">
    <x-json-ld :data="\App\Services\Seo::itemList($businesses->getCollection(), $category->name.' on Localist')" />
    <div class="mx-auto max-w-[75rem] px-4 py-10">
        <nav class="text-sm text-gray-500 dark:text-gray-400"><a href="{{ route('home') }}" class="hover:text-teal-700 dark:hover:text-teal-400">Home</a> / {{ $category->name }}</nav>
        <h1 class="mt-2 text-3xl font-bold">{{ $category->name }}</h1>
        <p class="mt-1 text-gray-500 dark:text-gray-400">{{ number_format($businesses->total()) }} businesses</p>

        @if ($cities->isNotEmpty())
            <div class="mt-6 flex flex-wrap gap-2">
                <a href="{{ route('category', $category) }}"
                    class="rounded-full px-3 py-1.5 text-sm font-medium {{ request('city') ? 'border border-gray-200 dark:border-gray-800 hover:border-teal-600' : 'bg-teal-700 text-white' }}">All cities</a>
                @foreach ($cities as $city)
                    <a href="{{ route('city.category', [$city, $category]) }}"
                        class="rounded-full border border-gray-200 dark:border-gray-800 px-3 py-1.5 text-sm font-medium hover:border-teal-600 hover:text-teal-700 dark:hover:text-teal-400">{{ $city->name }}</a>
                @endforeach
            </div>
        @endif

        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($businesses as $business)
                <x-business-card :business="$business" />
            @endforeach
        </div>

        <div class="mt-8">{{ $businesses->links() }}</div>
    </div>
</x-public-layout>
