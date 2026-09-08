<div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 sm:py-12">
    <h1 class="font-display text-2xl font-bold text-ink sm:text-3xl">Billing</h1>

    @if (! $business)
        <div class="surface-card mt-8 border-dashed p-10 text-center">
            <p class="font-display text-lg font-bold text-ink">No listing yet</p>
            <a href="{{ route('home') }}" class="mt-3 inline-block font-medium text-brand hover:underline">Claim your listing first</a>
        </div>
    @else
        <p class="mt-1.5 text-ink-muted">Current plan: <span class="font-semibold text-ink">{{ $business->plan->name }}</span></p>

        @error('billing') <p class="mt-4 rounded-lg bg-accent-soft px-4 py-3 text-sm font-medium text-accent-text">{{ $message }}</p> @enderror
        <span x-data="{ show: false }" x-show="show" x-cloak x-on:saved.window="show = true; setTimeout(() => show = false, 3000)"
            class="mt-4 inline-block rounded-lg bg-success-soft px-3 py-1.5 text-sm font-medium text-success-text">Plan updated</span>

        <div class="mt-8 grid gap-4 sm:grid-cols-3">
            @foreach ($plans as $plan)
                <div @class([
                    'flex flex-col surface-card p-5',
                    'border-brand ring-1 ring-brand' => $plan->id === $business->plan_id,
                ])>
                    <div class="flex items-center justify-between gap-2">
                        <h2 class="font-display text-lg font-bold text-ink">{{ $plan->name }}</h2>
                        @if ($plan->priority_rank > 0)
                            <span class="featured-flag">Ranks higher</span>
                        @endif
                    </div>
                    <p class="mt-2 text-2xl font-bold tabular-nums text-ink">RM{{ $plan->price_monthly }}<span class="text-sm font-normal text-ink-muted">/mo</span></p>
                    <ul class="mt-3 flex-1 space-y-1.5 text-sm text-ink-muted">
                        <li>Up to {{ $plan->max_photos }} photos</li>
                        <li>{{ $plan->allows_website ? 'Website link shown' : 'No website link' }}</li>
                        <li>{{ $plan->priority_rank > 0 ? 'Featured badge + priority placement' : 'Standard placement' }}</li>
                    </ul>
                    <div class="mt-5">
                        @if ($plan->id === $business->plan_id)
                            <span class="inline-block w-full rounded-lg bg-sunken px-4 py-2 text-center text-sm font-semibold text-ink-muted">Current plan</span>
                        @elseif ($plan->price_monthly === 0)
                            <button wire:click="downgradeToFree" wire:confirm="Downgrade to Free? Your listing loses featured placement."
                                class="btn btn-ghost w-full">Downgrade</button>
                        @else
                            <button wire:click="checkout({{ $plan->id }})" wire:loading.attr="disabled"
                                class="btn btn-primary w-full disabled:opacity-50">Upgrade via Stripe</button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        @if ($invoices->isNotEmpty())
            <h2 class="mt-10 font-display text-lg font-bold text-ink">Invoices</h2>
            <ul class="surface-card mt-3 divide-y divide-line overflow-hidden">
                @foreach ($invoices as $invoice)
                    <li class="flex items-center justify-between gap-4 p-4 text-sm text-ink">
                        <span>{{ $invoice->date()->toFormattedDateString() }}</span>
                        <span class="tabular-nums">{{ $invoice->total() }}</span>
                        <a href="{{ $invoice->hosted_invoice_url }}" target="_blank" class="font-medium text-brand hover:underline">View</a>
                    </li>
                @endforeach
            </ul>
        @endif
    @endif
</div>
