@props(['business'])

<a href="{{ route('business', $business) }}"
    class="flex min-w-0 gap-4 rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 shadow-sm transition hover:border-teal-600 hover:shadow-md">
    <div class="flex size-14 shrink-0 items-center justify-center rounded-lg bg-teal-700/10 dark:bg-teal-400/10 text-xl font-bold text-teal-700 dark:text-teal-400">
        {{ mb_substr($business->name, 0, 1) }}
    </div>
    <div class="min-w-0">
        <div class="flex items-center gap-2">
            <h3 class="truncate font-semibold">{{ $business->name }}</h3>
            @if ($business->isFeatured())
                <span class="shrink-0 rounded-full bg-amber-100 dark:bg-amber-400/10 px-2 py-0.5 text-xs font-semibold text-amber-700 dark:text-amber-300">Featured</span>
            @endif
        </div>
        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{{ $business->category->name }} · {{ $business->city->name }}</p>
        @if ($business->address)
            <p class="mt-1 truncate text-sm text-gray-500 dark:text-gray-400">{{ $business->address }}</p>
        @endif
    </div>
</a>
