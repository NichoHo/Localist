<div class="mx-auto max-w-4xl px-4 py-10">
    <h1 class="text-2xl font-bold">Billing</h1>

    @if (! $business)
        <div class="mt-8 rounded-lg border border-dashed border-gray-300 dark:border-gray-700 p-10 text-center">
            <p class="text-lg font-semibold">No listing yet</p>
            <a href="{{ route('home') }}" class="mt-4 inline-block font-medium text-teal-700 dark:text-teal-400 hover:underline">Claim your listing first</a>
        </div>
    @else
        <p class="mt-1 text-gray-500 dark:text-gray-400">Current plan: <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $business->plan->name }}</span></p>

        @error('billing') <p class="mt-4 rounded-md bg-amber-50 p-3 text-sm font-medium text-amber-700 dark:text-amber-300">{{ $message }}</p> @enderror
        <span x-data="{ show: false }" x-show="show" x-cloak x-on:saved.window="show = true; setTimeout(() => show = false, 3000)"
            class="mt-4 inline-block rounded-md bg-green-50 dark:bg-green-900/30 px-3 py-1.5 text-sm font-medium text-green-700 dark:text-green-300">Plan updated ✓</span>

        <div class="mt-8 grid gap-4 sm:grid-cols-3">
            @foreach ($plans as $plan)
                <div class="flex flex-col rounded-lg border p-5 shadow-sm {{ $plan->id === $business->plan_id ? 'border-teal-600 ring-1 ring-teal-600' : 'border-gray-200 dark:border-gray-800' }}">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold">{{ $plan->name }}</h2>
                        @if ($plan->priority_rank > 0)
                            <span class="rounded-full bg-amber-100 dark:bg-amber-400/10 px-2 py-0.5 text-xs font-semibold text-amber-700 dark:text-amber-300">Ranks higher</span>
                        @endif
                    </div>
                    <p class="mt-2 text-2xl font-bold tabular-nums">RM{{ $plan->price_monthly }}<span class="text-sm font-normal text-gray-500 dark:text-gray-400">/mo</span></p>
                    <ul class="mt-3 flex-1 space-y-1 text-sm text-gray-600 dark:text-gray-300">
                        <li>Up to {{ $plan->max_photos }} photos</li>
                        <li>{{ $plan->allows_website ? 'Website link shown' : 'No website link' }}</li>
                        <li>{{ $plan->priority_rank > 0 ? 'Featured badge + priority placement' : 'Standard placement' }}</li>
                    </ul>
                    <div class="mt-4">
                        @if ($plan->id === $business->plan_id)
                            <span class="inline-block w-full rounded-md bg-gray-100 dark:bg-gray-800 px-4 py-2 text-center text-sm font-semibold text-gray-500 dark:text-gray-400">Current plan</span>
                        @elseif ($plan->price_monthly === 0)
                            <button wire:click="downgradeToFree" wire:confirm="Downgrade to Free? Your listing loses featured placement."
                                class="w-full rounded-md border border-gray-300 dark:border-gray-700 px-4 py-2 text-sm font-semibold hover:border-teal-600">Downgrade</button>
                        @else
                            <button wire:click="checkout({{ $plan->id }})" wire:loading.attr="disabled"
                                class="w-full rounded-md bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800 disabled:opacity-50">
                                Upgrade via Stripe
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        @if ($invoices->isNotEmpty())
            <h2 class="mt-10 text-lg font-semibold">Invoices</h2>
            <ul class="mt-3 divide-y divide-gray-200 dark:divide-gray-800 rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900">
                @foreach ($invoices as $invoice)
                    <li class="flex items-center justify-between p-4 text-sm">
                        <span>{{ $invoice->date()->toFormattedDateString() }}</span>
                        <span class="tabular-nums">{{ $invoice->total() }}</span>
                        <a href="{{ $invoice->hosted_invoice_url }}" target="_blank" class="font-medium text-teal-700 dark:text-teal-400 hover:underline">View</a>
                    </li>
                @endforeach
            </ul>
        @endif
    @endif
</div>
