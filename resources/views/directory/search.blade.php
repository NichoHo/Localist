<x-public-layout :title="($q ? 'Search: '.$q : 'Search').' | Localist'" :noindex="true">
    <div class="mx-auto max-w-[75rem] px-4 py-10">
        <h1 class="text-3xl font-bold">Search</h1>
        <form action="{{ route('search') }}" method="get" class="mt-4 flex max-w-xl gap-2">
            <input type="search" name="q" value="{{ $q }}" placeholder="Search businesses…"
                class="flex-1 rounded-md border-gray-300 dark:border-gray-700 focus:border-teal-600 focus:ring-teal-600 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-500">
            <button class="rounded-md bg-teal-700 px-5 py-2 font-semibold text-white hover:bg-teal-800">Search</button>
        </form>

        <p class="mt-6 text-gray-500 dark:text-gray-400">{{ number_format($businesses->total()) }} results{{ $q ? ' for "'.$q.'"' : '' }}</p>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($businesses as $business)
                <x-business-card :business="$business" />
            @endforeach
        </div>

        <div class="mt-8">{{ $businesses->links() }}</div>
    </div>
</x-public-layout>
