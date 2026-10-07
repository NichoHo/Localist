@php
    $price = fn ($plan) => 'RM'.number_format($plan->price_monthly);
    $placement = fn ($plan) => match (true) {
        $plan->priority_rank >= 2 => 'Above Featured listings',
        $plan->priority_rank === 1 => 'Above Free listings',
        default => 'Standard',
    };
    $rows = [
        'Photos' => fn ($p) => 'Up to '.$p->max_photos,
        'Website link' => fn ($p) => $p->allows_website ? 'Shown on listing' : 'Not shown',
        'Featured badge' => fn ($p) => $p->priority_rank > 0 ? 'Yes' : 'No',
        'Search placement' => $placement,
    ];
@endphp

<div class="mx-auto max-w-[70rem] px-4 py-10 sm:px-6 sm:py-12">
    <h1 class="font-display text-2xl font-bold text-ink sm:text-3xl">Billing</h1>

    @if (! $business)
        <div class="surface-card mt-8 border-dashed p-10 text-center">
            <p class="font-display text-lg font-bold text-ink">No listing yet</p>
            <p class="mx-auto mt-2 max-w-md text-ink-muted">Plans apply to a listing. Claim yours first, then come back to pick one.</p>
            <a href="{{ route('home') }}" class="btn btn-primary mt-6">Browse the directory</a>
        </div>
    @else
        <p class="mt-1.5 text-ink-muted">{{ $business->name }}. Pick the placement that fits, and see every invoice in one place.</p>

        @error('billing') <p class="mt-4 rounded-md bg-danger-soft px-4 py-3 text-sm font-medium text-danger-text" role="alert">{{ $message }}</p> @enderror
        <span x-data="{ show: false }" x-show="show" x-cloak x-on:saved.window="show = true; setTimeout(() => show = false, 3000)" role="status"
            class="mt-4 inline-block rounded-md bg-success-soft px-3 py-1.5 text-sm font-medium text-success-text">Plan updated</span>

        {{-- Where you stand today, and what a paid plan changes in the directory --}}
        <div class="mt-8 grid gap-6 lg:grid-cols-[1.15fr_1fr]">
            <section class="surface-card p-6" aria-labelledby="current-plan">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-sm text-ink-muted" id="current-plan">Current plan</p>
                        <p class="mt-1 flex items-center gap-2.5 font-display text-2xl font-bold text-ink">
                            {{ $business->plan->name }}
                            @if ($business->plan->priority_rank > 0)
                                <span class="featured-flag">Featured</span>
                            @endif
                        </p>
                    </div>
                    <p class="text-right">
                        <span class="font-display text-2xl font-bold tabular-nums text-ink">{{ $price($business->plan) }}</span>
                        <span class="text-sm text-ink-muted">/month</span>
                    </p>
                </div>

                <p class="mt-3 text-sm text-ink-muted">
                    @if ($subscription?->onGracePeriod())
                        Subscription ends {{ $subscription->ends_at->toFormattedDateString() }}. Your listing then returns to Free.
                    @elseif ($subscription?->active())
                        Subscription active. Billed monthly through Stripe.
                    @else
                        No active subscription. The Free plan never expires.
                    @endif
                </p>

                <dl class="mt-6 grid grid-cols-2 gap-x-6 gap-y-5 border-t border-line pt-6">
                    <div class="col-span-2">
                        <dt class="flex items-baseline justify-between text-sm text-ink-muted">
                            Photos used
                            <span class="font-semibold tabular-nums text-ink">{{ $photoCount }} of {{ $business->plan->max_photos }}</span>
                        </dt>
                        <dd class="mt-2" role="meter" aria-valuemin="0" aria-valuemax="{{ $business->plan->max_photos }}" aria-valuenow="{{ $photoCount }}" aria-label="Photos used">
                            <div class="h-1.5 overflow-hidden rounded-full bg-sunken">
                                <div class="h-full rounded-full bg-brand" style="width: {{ $business->plan->max_photos ? min(100, round($photoCount / $business->plan->max_photos * 100)) : 0 }}%"></div>
                            </div>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm text-ink-muted">Listing views</dt>
                        <dd class="mt-1 text-xl font-bold tabular-nums text-ink">{{ number_format($business->views_count) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-ink-muted">Enquiries received</dt>
                        <dd class="mt-1 text-xl font-bold tabular-nums text-ink">{{ number_format($leadCount) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-ink-muted">Website link</dt>
                        <dd class="mt-1 text-sm font-semibold text-ink">{{ $business->plan->allows_website ? 'Shown on your listing' : 'Hidden on Free' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-ink-muted">Search placement</dt>
                        <dd class="mt-1 text-sm font-semibold text-ink">{{ $placement($business->plan) }}</dd>
                    </div>
                </dl>
            </section>

            <section aria-labelledby="placement-preview">
                <h2 class="font-display text-lg font-bold text-ink" id="placement-preview">
                    {{ $upgradePreview ? 'How a paid plan changes your listing' : 'How your listing appears' }}
                </h2>
                <p class="mt-1 text-sm text-ink-muted">
                    {{ $upgradePreview ? 'Featured listings get the yellow icon and a badge, and sort above free ones in every category and city.' : 'This is the row visitors see in search, category and city pages.' }}
                </p>

                <div class="mt-4 space-y-3">
                    <div>
                        @if ($upgradePreview)<p class="mb-1.5 text-xs font-semibold text-ink-subtle">Today, on Free</p>@endif
                        <div class="business-list"><x-business-card :business="$business" /></div>
                    </div>
                    @if ($upgradePreview)
                        <div>
                            <p class="mb-1.5 text-xs font-semibold text-ink-subtle">With {{ $upgradePreview->plan->name }}</p>
                            <div class="business-list"><x-business-card :business="$upgradePreview" /></div>
                        </div>
                    @endif
                </div>
            </section>
        </div>

        {{-- Plan comparison --}}
        <section class="mt-12" aria-labelledby="compare-plans">
            <h2 class="font-display text-xl font-bold text-ink" id="compare-plans">Compare plans</h2>
            <p class="mt-1 max-w-2xl text-sm text-ink-muted">Prices are per month in Malaysian ringgit. Change or cancel whenever you like.</p>
            @if ($testMode)
                <p class="mt-3 max-w-2xl rounded-md border border-accent-line bg-accent-soft px-4 py-2.5 text-sm text-accent-text">
                    Stripe test mode is on. Pay with card <span class="font-mono font-semibold">4242 4242 4242 4242</span>, any future expiry and any CVC. No real money moves.
                </p>
            @endif

            {{-- md and up: one comparison table so the rows line up --}}
            <div class="surface-card mt-5 hidden overflow-hidden md:block">
                <table class="w-full table-fixed text-left text-sm">
                    <caption class="sr-only">Plan comparison</caption>
                    <thead>
                        <tr class="border-b border-line">
                            <td class="w-[22%]"></td>
                            @foreach ($plans as $plan)
                                <th scope="col" @class(['p-5 align-top font-normal', 'bg-brand-soft' => $plan->id === $business->plan_id])>
                                    <span class="block font-display text-lg font-bold text-ink">{{ $plan->name }}</span>
                                    <span class="mt-1 block">
                                        <span class="text-xl font-bold tabular-nums text-ink">{{ $price($plan) }}</span>
                                        <span class="text-ink-muted">/mo</span>
                                    </span>
                                    <span class="mt-4 block">@include('livewire.portal.partials.plan-action', ['plan' => $plan, 'business' => $business])</span>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach ($rows as $label => $value)
                            <tr>
                                <th scope="row" class="px-5 py-3.5 font-medium text-ink-muted">{{ $label }}</th>
                                @foreach ($plans as $plan)
                                    <td @class(['px-5 py-3.5 text-ink', 'bg-brand-soft' => $plan->id === $business->plan_id])>{{ $value($plan) }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Below md: one block per plan --}}
            <div class="mt-5 space-y-4 md:hidden">
                @foreach ($plans as $plan)
                    <div @class(['surface-card p-5', 'border-brand ring-1 ring-brand' => $plan->id === $business->plan_id])>
                        <div class="flex items-baseline justify-between gap-3">
                            <h3 class="font-display text-lg font-bold text-ink">{{ $plan->name }}</h3>
                            <p><span class="text-xl font-bold tabular-nums text-ink">{{ $price($plan) }}</span><span class="text-sm text-ink-muted">/mo</span></p>
                        </div>
                        <dl class="mt-4 divide-y divide-line-subtle text-sm">
                            @foreach ($rows as $label => $value)
                                <div class="flex justify-between gap-4 py-2.5">
                                    <dt class="text-ink-muted">{{ $label }}</dt>
                                    <dd class="text-right font-medium text-ink">{{ $value($plan) }}</dd>
                                </div>
                            @endforeach
                        </dl>
                        <div class="mt-4">@include('livewire.portal.partials.plan-action', ['plan' => $plan, 'business' => $business])</div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Invoices + payment notes --}}
        <div class="mt-12 grid gap-6 lg:grid-cols-[1.4fr_1fr]">
            <section aria-labelledby="invoices">
                <h2 class="font-display text-xl font-bold text-ink" id="invoices">Invoices</h2>
                @if ($invoices->isEmpty())
                    <div class="surface-card mt-4 border-dashed p-8 text-center">
                        <p class="font-semibold text-ink">No invoices yet</p>
                        <p class="mx-auto mt-1 max-w-sm text-sm text-ink-muted">Stripe issues one after each payment. It will show up here with a link to the receipt.</p>
                    </div>
                @else
                    <ul class="surface-card mt-4 divide-y divide-line overflow-hidden">
                        @foreach ($invoices as $invoice)
                            <li class="grid grid-cols-[1fr_auto] items-center gap-x-4 gap-y-1 px-5 py-4 text-sm sm:grid-cols-[1fr_auto_auto_auto]">
                                <span class="font-medium text-ink">{{ $invoice->date()->toFormattedDateString() }}</span>
                                <span class="tabular-nums text-ink sm:text-right">{{ $invoice->total() }}</span>
                                <span @class(['status-pill', 'status-open' => $invoice->status === 'paid', 'status-shut' => $invoice->status !== 'paid'])>{{ ucfirst($invoice->status) }}</span>
                                <a href="{{ $invoice->hosted_invoice_url }}" target="_blank" rel="noopener" class="text-right font-medium text-brand hover:underline">View</a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <section aria-labelledby="good-to-know">
                <h2 class="font-display text-xl font-bold text-ink" id="good-to-know">Good to know</h2>
                <dl class="surface-card mt-4 divide-y divide-line-subtle px-5 text-sm">
                    <div class="py-4">
                        <dt class="font-semibold text-ink">Who handles the card?</dt>
                        <dd class="mt-1 text-ink-muted">Stripe. You enter card details on their page, so Localist never sees or stores them.</dd>
                    </div>
                    <div class="py-4">
                        <dt class="font-semibold text-ink">When does my plan change?</dt>
                        <dd class="mt-1 text-ink-muted">As soon as Stripe confirms the payment. You come straight back to this page.</dd>
                    </div>
                    <div class="py-4">
                        <dt class="font-semibold text-ink">What happens if I downgrade?</dt>
                        <dd class="mt-1 text-ink-muted">Your subscription is cancelled right away and the listing goes back to standard placement.</dd>
                    </div>
                </dl>
            </section>
        </div>
    @endif
</div>
