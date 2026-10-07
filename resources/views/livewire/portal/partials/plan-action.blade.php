@if ($plan->id === $business->plan_id)
    <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-ink">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="size-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
        Your plan
    </span>
@elseif ($plan->price_monthly === 0)
    <button wire:click="downgradeToFree" wire:confirm="Downgrade to Free? Your listing loses featured placement." class="btn btn-ghost w-full">Downgrade to Free</button>
@else
    <button wire:click="checkout({{ $plan->id }})" wire:loading.attr="disabled" wire:target="checkout({{ $plan->id }})"
        class="btn btn-accent w-full disabled:opacity-60">
        <span wire:loading.remove wire:target="checkout({{ $plan->id }})">{{ $plan->priority_rank > $business->plan->priority_rank ? 'Upgrade' : 'Switch' }} to {{ $plan->name }}</span>
        <span wire:loading wire:target="checkout({{ $plan->id }})">Opening Stripe&hellip;</span>
    </button>
@endif
