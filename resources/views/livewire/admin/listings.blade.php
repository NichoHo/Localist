<div class="mx-auto max-w-6xl px-4 py-10">
    <h1 class="text-2xl font-bold">Admin — Listings</h1>

    <div class="mt-6 flex flex-wrap items-center gap-2">
        @foreach (['pending', 'published', 'draft', 'all'] as $tab)
            <button wire:click="setStatus('{{ $tab }}')"
                class="rounded-full px-3 py-1.5 text-sm font-medium capitalize {{ $status === $tab ? 'bg-teal-700 text-white' : 'border border-gray-300 dark:border-gray-700 hover:border-teal-600' }}">
                {{ $tab }}
                @if ($tab !== 'all')
                    <span class="tabular-nums">({{ $counts[$tab] ?? 0 }})</span>
                @endif
            </button>
        @endforeach
        <input type="search" wire:model.live.debounce.400ms="search" placeholder="Search name…"
            class="ml-auto rounded-md border-gray-300 text-sm focus:border-teal-600 focus:ring-teal-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
    </div>

    @if ($businesses->isEmpty())
        <div class="mt-8 rounded-lg border border-dashed border-gray-300 p-10 text-center text-gray-500 dark:border-gray-700 dark:text-gray-400">
            No {{ $status === 'all' ? '' : $status }} listings.
        </div>
    @else
        <div class="mt-6 overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-800">
            <table class="w-full divide-y divide-gray-200 bg-white text-sm dark:divide-gray-800 dark:bg-gray-900">
                <thead class="text-left text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3 font-medium">Business</th>
                        <th class="px-4 py-3 font-medium">Category / City</th>
                        <th class="px-4 py-3 font-medium">Plan</th>
                        <th class="px-4 py-3 font-medium">Owner</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @foreach ($businesses as $business)
                        <tr wire:key="biz-{{ $business->id }}">
                            <td class="px-4 py-3">
                                <a href="{{ route('business', $business) }}" class="font-medium text-teal-700 hover:underline dark:text-teal-400">{{ $business->name }}</a>
                            </td>
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $business->category->name }} · {{ $business->city->name }}</td>
                            <td class="px-4 py-3">{{ $business->plan->name }}</td>
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $business->user?->email ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2 py-0.5 text-xs font-semibold capitalize
                                    {{ $business->status === 'published' ? 'bg-green-100 text-green-700 dark:bg-green-400/10 dark:text-green-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-400/10 dark:text-amber-300' }}">
                                    {{ $business->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if ($business->status === 'published')
                                    <button wire:click="unpublish({{ $business->id }})" wire:confirm="Unpublish this listing?"
                                        class="rounded-md border border-gray-300 px-2.5 py-1 text-xs font-medium hover:border-red-500 dark:border-gray-700">Unpublish</button>
                                @else
                                    <button wire:click="approve({{ $business->id }})"
                                        class="rounded-md bg-teal-700 px-2.5 py-1 text-xs font-semibold text-white hover:bg-teal-800">Approve</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $businesses->links() }}</div>
    @endif
</div>
