<div class="mx-auto max-w-[70rem] px-4 py-10 sm:px-6 sm:py-12">
    @if (! $business)
        <h1 class="font-display text-2xl font-bold text-ink sm:text-3xl">Dashboard</h1>
        <div class="surface-card mt-8 border-dashed p-10 text-center">
            <p class="font-display text-lg font-bold text-ink">You haven&rsquo;t claimed a listing yet</p>
            <p class="mx-auto mt-2 max-w-md text-ink-muted">Find your business in the directory and claim it to start managing it here.</p>
            <a href="{{ route('home') }}" class="btn btn-primary mt-6">Browse the directory</a>
        </div>
    @else
        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="font-display text-2xl font-bold text-ink sm:text-3xl">Dashboard</h1>
                <p class="mt-1.5 text-ink-muted">{{ $business->name }} · {{ $business->category->name }} · {{ $business->city->name }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('portal.edit') }}" class="btn btn-primary">Edit listing</a>
                <a href="{{ route('business', $business) }}" target="_blank" class="btn btn-ghost">
                    View public page
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
                </a>
            </div>
        </div>

        {{-- Stat tiles --}}
        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="surface-card p-5">
                <p class="text-sm text-ink-muted">Status</p>
                <p class="mt-2 flex items-center gap-2 text-xl font-bold capitalize text-ink">
                    <span @class([
                        'size-2.5 rounded-full',
                        'bg-success' => $business->status === 'published',
                        'bg-accent' => $business->status === 'pending',
                        'bg-ink-subtle' => $business->status === 'draft',
                    ])></span>
                    {{ $business->status }}
                </p>
            </div>
            <div class="surface-card p-5">
                <p class="text-sm text-ink-muted">Total views</p>
                {{-- ponytail: lifetime views only; per-month needs a views log table --}}
                <p class="mt-2 text-2xl font-bold tabular-nums text-ink">{{ number_format($business->views_count) }}</p>
            </div>
            <a href="{{ route('portal.leads') }}" class="surface-card hover-lift group p-5">
                <p class="flex items-center justify-between text-sm text-ink-muted">
                    Leads
                    @if ($unreadLeads > 0)
                        <span class="rounded-full bg-brand px-1.5 text-xs font-semibold tabular-nums text-on-brand">{{ $unreadLeads }} new</span>
                    @endif
                </p>
                <p class="mt-2 text-2xl font-bold tabular-nums text-ink">{{ number_format($totalLeads) }}</p>
            </a>
            <div class="surface-card p-5">
                <p class="text-sm text-ink-muted">Plan</p>
                <p class="mt-2 flex items-center gap-2 text-xl font-bold text-ink">
                    {{ $business->plan->name }}
                    @if ($business->plan->priority_rank > 0)
                        <span class="featured-flag">Featured</span>
                    @endif
                </p>
            </div>
        </div>

        {{-- Main grid: recent enquiries + listing panel --}}
        <div class="mt-6 grid gap-6 lg:grid-cols-[1.6fr_1fr]">
            <section class="surface-card p-6">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="font-display text-lg font-bold text-ink">Recent enquiries</h2>
                    <a href="{{ route('portal.leads') }}" class="text-sm font-medium text-brand hover:underline">View all</a>
                </div>

                @if ($recentLeads->isEmpty())
                    <div class="mt-4 rounded-xl border border-dashed border-line p-8 text-center text-sm text-ink-muted">
                        No enquiries yet. They&rsquo;ll appear here when visitors contact you from your listing.
                    </div>
                @else
                    <ul class="mt-2 divide-y divide-line-subtle">
                        @foreach ($recentLeads as $lead)
                            <li class="flex items-start gap-3 py-3.5">
                                <span class="grid size-9 shrink-0 place-items-center rounded-full bg-brand-soft text-xs font-bold uppercase text-brand">{{ collect(explode(' ', $lead->name))->take(2)->map(fn ($w) => mb_substr($w, 0, 1))->implode('') }}</span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <p class="truncate font-medium text-ink">{{ $lead->name }}</p>
                                        @unless ($lead->read_at)
                                            <span class="size-1.5 shrink-0 rounded-full bg-brand" title="Unread"></span>
                                        @endunless
                                        <span class="ml-auto shrink-0 text-xs text-ink-subtle">{{ $lead->created_at->diffForHumans(['short' => true]) }}</span>
                                    </div>
                                    <p class="mt-0.5 truncate text-sm text-ink-muted">{{ $lead->message }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <aside class="space-y-6">
                @php
                    $checks = [
                        ['Description added', filled($business->description), route('portal.edit')],
                        ['Phone number', filled($business->phone), route('portal.edit')],
                        ['Website link', filled($business->website), route('portal.edit')],
                        ['At least one photo', $photoCount > 0, route('portal.photos')],
                        ['Opening hours', filled($business->hours), route('portal.edit')],
                    ];
                    $done = collect($checks)->filter(fn ($c) => $c[1])->count();
                @endphp
                <section class="surface-card p-6">
                    <div class="flex items-center justify-between gap-4">
                        <h2 class="font-display text-lg font-bold text-ink">Listing completeness</h2>
                        <span class="text-sm font-semibold tabular-nums text-ink-muted">{{ $done }}/{{ count($checks) }}</span>
                    </div>
                    <div class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-sunken">
                        <div class="h-full rounded-full bg-brand transition-all" style="width: {{ round($done / count($checks) * 100) }}%"></div>
                    </div>
                    <ul class="mt-4 space-y-1">
                        @foreach ($checks as [$label, $ok, $href])
                            <li>
                                @if ($ok)
                                    <div class="flex items-center gap-2.5 py-1 text-sm">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" class="size-4 shrink-0 text-success" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                        <span class="text-ink">{{ $label }}</span>
                                    </div>
                                @else
                                    <a href="{{ $href }}" class="group flex items-center gap-2.5 py-1 text-sm">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-4 shrink-0 text-ink-subtle transition group-hover:text-brand" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                        <span class="text-ink-muted transition group-hover:text-brand">{{ $label }}</span>
                                    </a>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                    <div class="mt-5 flex flex-col gap-2 border-t border-line-subtle pt-5">
                        <a href="{{ route('portal.photos') }}" class="btn btn-ghost w-full justify-between">
                            Manage photos
                            <span class="text-ink-subtle tabular-nums">{{ $photoCount }}/{{ $business->plan->max_photos }}</span>
                        </a>
                    </div>
                </section>

                @if ($business->plan->priority_rank === 0)
                    <section class="overflow-hidden rounded-md">
                        <div class="bg-accent p-6">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-on-accent/65">Upgrade</p>
                            <h2 class="mt-2 font-display text-lg font-bold text-on-accent">Get featured placement</h2>
                            <p class="mt-1.5 text-sm leading-relaxed text-on-accent/75">Rank above free listings, show your website link, and add more photos.</p>
                            <a href="{{ route('portal.billing') }}" class="btn btn-on-accent mt-4 focus-visible:ring-offset-accent">See plans</a>
                        </div>
                    </section>
                @endif
            </aside>
        </div>
    @endif
</div>
