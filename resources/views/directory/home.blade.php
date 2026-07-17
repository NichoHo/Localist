<x-public-layout>
    <section class="bg-gradient-to-b from-teal-700 to-teal-800 px-4 py-16 text-center text-white">
        <h1 class="text-3xl font-bold sm:text-4xl">Find trusted local services near you</h1>
        <p class="mx-auto mt-3 max-w-xl text-teal-100">Plumbers, electricians, cleaners, tutors and more — across {{ $cityCount }} Malaysian cities.</p>
        <form action="{{ route('search') }}" method="get" class="mx-auto mt-8 flex max-w-xl gap-2">
            <input type="search" name="q" placeholder="What do you need help with?"
                class="flex-1 rounded-md border-0 text-gray-900 focus:ring-2 focus:ring-amber-400 dark:bg-gray-900 dark:text-gray-100">
            <button class="rounded-md bg-amber-500 px-5 py-2.5 font-semibold text-white hover:bg-amber-600">Search</button>
        </form>
    </section>

    <section class="mx-auto max-w-[75rem] px-4 py-12">
        <h2 class="text-2xl font-semibold">Browse by category</h2>
        <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
            @foreach ($categories as $category)
                <a href="{{ route('category', $category) }}"
                    class="rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 shadow-sm transition hover:border-teal-600 hover:shadow-md">
                    <p class="font-semibold">{{ $category->name }}</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ number_format($category->businesses_count) }} listings</p>
                </a>
            @endforeach
        </div>
    </section>

    @if ($featured->isNotEmpty())
        <section class="mx-auto max-w-[75rem] px-4 py-6">
            <h2 class="text-2xl font-semibold">Featured businesses</h2>
            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($featured as $business)
                    <x-business-card :business="$business" />
                @endforeach
            </div>
        </section>
    @endif

    <section class="mx-auto max-w-[75rem] px-4 py-12">
        <h2 class="text-2xl font-semibold">Popular cities</h2>
        <div class="mt-6 flex flex-wrap gap-2">
            @foreach ($cities as $city)
                <a href="{{ route('city', $city) }}"
                    class="rounded-full border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 px-4 py-2 text-sm font-medium hover:border-teal-600 hover:text-teal-700 dark:hover:text-teal-400">
                    {{ $city->name }} <span class="text-gray-400">({{ number_format($city->businesses_count) }})</span>
                </a>
            @endforeach
        </div>
    </section>
</x-public-layout>
