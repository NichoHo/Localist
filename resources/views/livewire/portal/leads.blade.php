<div class="mx-auto max-w-3xl px-4 py-10">
    <div class="flex items-center gap-3">
        <h1 class="text-2xl font-bold">Leads</h1>
        @if ($unread > 0)
            <span class="rounded-full bg-teal-700 px-2.5 py-0.5 text-sm font-semibold text-white tabular-nums">{{ $unread }} unread</span>
        @endif
    </div>

    @if (! $business)
        <div class="mt-8 rounded-lg border border-dashed border-gray-300 dark:border-gray-700 p-10 text-center">
            <p class="text-lg font-semibold">No listing yet</p>
            <a href="{{ route('home') }}" class="mt-4 inline-block font-medium text-teal-700 dark:text-teal-400 hover:underline">Claim your listing first</a>
        </div>
    @elseif ($leads->isEmpty())
        <div class="mt-8 rounded-lg border border-dashed border-gray-300 dark:border-gray-700 p-10 text-center text-gray-500 dark:text-gray-400">
            No enquiries yet. They'll appear here when visitors contact you from your listing page.
        </div>
    @else
        <ul class="mt-6 divide-y divide-gray-200 dark:divide-gray-800 rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900">
            @foreach ($leads as $lead)
                <li wire:key="lead-{{ $lead->id }}" class="p-4 {{ $lead->read_at ? 'bg-white dark:bg-gray-900' : 'bg-teal-50/50 dark:bg-teal-900/20' }}">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="font-semibold">
                                {{ $lead->name }}
                                @unless ($lead->read_at)
                                    <span class="ml-1 inline-block size-2 rounded-full bg-teal-600 align-middle"></span>
                                @endunless
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                <a href="mailto:{{ $lead->email }}" class="text-teal-700 dark:text-teal-400 hover:underline">{{ $lead->email }}</a>
                                @if ($lead->phone) · {{ $lead->phone }} @endif
                                · {{ $lead->created_at->diffForHumans() }}
                            </p>
                            <p class="mt-2 text-sm">{{ $lead->message }}</p>
                        </div>
                        <button wire:click="toggleRead({{ $lead->id }})"
                            class="shrink-0 rounded-md border border-gray-300 dark:border-gray-700 px-2.5 py-1 text-xs font-medium hover:border-teal-600">
                            {{ $lead->read_at ? 'Mark unread' : 'Mark read' }}
                        </button>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</div>
