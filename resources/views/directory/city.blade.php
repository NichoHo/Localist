<x-public-layout :title="'Local services in '.$city->name.' — Localist'" :description="'Browse trusted local service businesses in '.$city->name.', '.$city->region.' by category.'">
    <div class="mx-auto max-w-[75rem] px-4 py-10">
        <nav class="text-sm text-gray-500 dark:text-gray-400"><a href="{{ route('home') }}" class="hover:text-teal-700 dark:hover:text-teal-400">Home</a> / {{ $city->name }}</nav>
        <h1 class="mt-2 text-3xl font-bold">Local services in {{ $city->name }}</h1>
        <p class="mt-1 text-gray-500 dark:text-gray-400">{{ $city->region }}</p>

        <h2 class="mt-8 text-xl font-semibold">Browse by category</h2>
        <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
            @foreach ($categories as $category)
                <a href="{{ route('city.category', [$city, $category]) }}"
                    class="rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 shadow-sm transition hover:border-teal-600 hover:shadow-md">
                    <p class="font-semibold">{{ $category->name }}</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ number_format($category->businesses_count) }} in {{ $city->name }}</p>
                </a>
            @endforeach
        </div>

        @if ($featured->isNotEmpty())
            <h2 class="mt-10 text-xl font-semibold">Featured in {{ $city->name }}</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($featured as $business)
                    <x-business-card :business="$business" />
                @endforeach
            </div>
        @endif
    </div>
</x-public-layout>
