<div class="mx-auto max-w-5xl px-4 py-10">
    <h1 class="text-2xl font-bold">Dashboard</h1>

    @if (! $business)
        <div class="mt-8 rounded-lg border border-dashed border-gray-300 dark:border-gray-700 p-10 text-center">
            <p class="text-lg font-semibold">You haven't claimed a listing yet</p>
            <p class="mt-2 text-gray-500 dark:text-gray-400">Find your business in the directory and claim it to start managing it here.</p>
            <a href="{{ route('home') }}" class="mt-4 inline-block rounded-md bg-teal-700 px-4 py-2 font-semibold text-white hover:bg-teal-800">Browse the directory</a>
        </div>
    @else
        <p class="mt-1 text-gray-500 dark:text-gray-400">{{ $business->name }} · {{ $business->category->name }} · {{ $business->city->name }}</p>

        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm">
                <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                <p class="mt-1 text-xl font-semibold capitalize tabular-nums">{{ $business->status }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm">
                <p class="text-sm text-gray-500 dark:text-gray-400">Total views</p>
                {{-- ponytail: lifetime views only; per-month needs a views log table --}}
                <p class="mt-1 text-xl font-semibold tabular-nums">{{ number_format($business->views_count) }}</p>
            </div>
            <a href="{{ route('portal.leads') }}" class="rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm hover:border-teal-600">
                <p class="text-sm text-gray-500 dark:text-gray-400">Unread leads</p>
                <p class="mt-1 text-xl font-semibold tabular-nums">{{ $unreadLeads }}</p>
            </a>
            <div class="rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm">
                <p class="text-sm text-gray-500 dark:text-gray-400">Plan</p>
                <p class="mt-1 text-xl font-semibold">{{ $business->plan->name }}</p>
            </div>
        </div>

        <div class="mt-8 flex flex-wrap gap-3">
            <a href="{{ route('portal.edit') }}" class="rounded-md bg-teal-700 px-4 py-2 font-semibold text-white hover:bg-teal-800">Edit listing</a>
            <a href="{{ route('portal.photos') }}" class="rounded-md border border-gray-300 dark:border-gray-700 px-4 py-2 font-semibold hover:border-teal-600">Manage photos</a>
            <a href="{{ route('business', $business) }}" class="rounded-md border border-gray-300 dark:border-gray-700 px-4 py-2 font-semibold hover:border-teal-600">View public page</a>
        </div>
    @endif
</div>
