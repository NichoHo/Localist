<div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 sm:py-12">
    <h1 class="font-display text-2xl font-bold text-ink sm:text-3xl">Admin listings</h1>

    <div class="mt-6 flex flex-wrap items-center gap-2">
        @foreach (['pending', 'published', 'draft', 'all'] as $tab)
            <button wire:click="setStatus('{{ $tab }}')" @class(['chip capitalize', 'chip-active' => $status === $tab])>
                {{ $tab }}
                @if ($tab !== 'all')
                    <span class="tabular-nums">({{ $counts[$tab] ?? 0 }})</span>
                @endif
            </button>
        @endforeach
        <input type="search" wire:model.live.debounce.400ms="search" placeholder="Search name…" class="field w-full sm:ml-auto sm:w-64">
    </div>

    @if ($businesses->isEmpty())
        <div class="surface-card mt-8 border-dashed p-10 text-center text-ink-muted">
            No {{ $status === 'all' ? '' : $status }} listings.
        </div>
    @else
        <div class="surface-card mt-6 overflow-x-auto">
            <table class="w-full min-w-[46rem] divide-y divide-line text-sm">
                <thead class="text-left text-ink-muted">
                    <tr>
                        <th class="px-4 py-3 font-medium">Business</th>
                        <th class="px-4 py-3 font-medium">Category / City</th>
                        <th class="px-4 py-3 font-medium">Plan</th>
                        <th class="px-4 py-3 font-medium">Owner</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @foreach ($businesses as $business)
                        <tr wire:key="biz-{{ $business->id }}">
                            <td class="px-4 py-3">
                                <a href="{{ route('business', $business) }}" class="font-medium text-brand hover:underline">{{ $business->name }}</a>
                            </td>
                            <td class="px-4 py-3 text-ink-muted">{{ $business->category->name }} · {{ $business->city->name }}</td>
                            <td class="px-4 py-3 text-ink">{{ $business->plan->name }}</td>
                            <td class="px-4 py-3 text-ink-muted">{{ $business->user?->email ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <span @class([
                                    'rounded-full px-2 py-0.5 text-xs font-semibold capitalize ring-1 ring-inset',
                                    'bg-success-soft text-success-text ring-success/20' => $business->status === 'published',
                                    'bg-accent-soft text-accent-text ring-accent-line' => $business->status !== 'published',
                                ])>{{ $business->status }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if ($business->status === 'published')
                                    <button wire:click="unpublish({{ $business->id }})" wire:confirm="Unpublish this listing?"
                                        class="rounded-lg border border-line px-2.5 py-1 text-xs font-medium text-ink-muted transition hover:border-danger hover:text-danger-text">Unpublish</button>
                                @else
                                    <button wire:click="approve({{ $business->id }})"
                                        class="btn btn-primary px-2.5 py-1 text-xs">Approve</button>
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
