<div class="mx-auto max-w-3xl px-4 py-10 sm:px-6 sm:py-12">
    <div class="flex items-center gap-3">
        <h1 class="font-display text-2xl font-bold text-ink sm:text-3xl">Leads</h1>
        @if ($unread > 0)
            <span class="rounded-full bg-brand px-2.5 py-0.5 text-sm font-semibold tabular-nums text-on-brand">{{ $unread }} unread</span>
        @endif
    </div>

    @if (! $business)
        <div class="surface-card mt-8 border-dashed p-10 text-center">
            <p class="font-display text-lg font-bold text-ink">No listing yet</p>
            <a href="{{ route('home') }}" class="mt-3 inline-block font-medium text-brand hover:underline">Claim your listing first</a>
        </div>
    @elseif ($leads->isEmpty())
        <div class="surface-card mt-8 border-dashed p-10 text-center text-ink-muted">
            No enquiries yet. They&rsquo;ll appear here when visitors contact you from your listing page.
        </div>
    @else
        <ul class="surface-card mt-6 divide-y divide-line overflow-hidden">
            @foreach ($leads as $lead)
                <li wire:key="lead-{{ $lead->id }}" class="p-4 sm:p-5 {{ $lead->read_at ? '' : 'bg-brand-soft' }}">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="font-semibold text-ink">
                                {{ $lead->name }}
                                @unless ($lead->read_at)
                                    <span class="ml-1 inline-block size-2 rounded-full bg-brand align-middle"></span>
                                @endunless
                            </p>
                            <p class="text-sm text-ink-muted">
                                <a href="mailto:{{ $lead->email }}" class="text-brand hover:underline">{{ $lead->email }}</a>
                                @if ($lead->phone) · {{ $lead->phone }} @endif
                                · {{ $lead->created_at->diffForHumans() }}
                            </p>
                            <p class="mt-2 text-sm text-ink">{{ $lead->message }}</p>
                        </div>
                        <button wire:click="toggleRead({{ $lead->id }})"
                            class="shrink-0 rounded-lg border border-line px-2.5 py-1 text-xs font-medium text-ink-muted transition hover:border-brand-line hover:bg-brand-soft hover:text-brand">
                            {{ $lead->read_at ? 'Mark unread' : 'Mark read' }}
                        </button>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</div>
