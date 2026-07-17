<?php

namespace App\Livewire\Portal;

use App\Models\Business;
use App\Models\Plan;
use Laravel\Cashier\Cashier;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Billing extends Component
{
    public ?Business $business = null;

    public function mount(): void
    {
        $this->business = auth()->user()->business();

        // Returning from Stripe Checkout: verify the session before applying the plan.
        if ($sessionId = request()->query('session_id')) {
            $this->applyCheckoutSession($sessionId);
        }
    }

    public function checkout(int $planId)
    {
        $plan = Plan::findOrFail($planId);
        $price = config("services.stripe.prices.{$plan->name}");

        if (! config('cashier.secret') || ! $price) {
            $this->addError('billing', 'Stripe is not configured. Add STRIPE_KEY, STRIPE_SECRET and STRIPE_PRICE_* to .env (test mode).');

            return;
        }

        return auth()->user()
            ->newSubscription('default', $price)
            ->checkout([
                'success_url' => route('portal.billing').'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('portal.billing'),
            ]);
    }

    public function downgradeToFree(): void
    {
        $user = auth()->user();
        if ($user->subscribed('default')) {
            $user->subscription('default')->cancelNow();
        }

        $this->business->update(['plan_id' => Plan::where('name', 'Free')->value('id')]);
        $this->dispatch('saved');
    }

    private function applyCheckoutSession(string $sessionId): void
    {
        // ponytail: checkout-callback plan sync; production renewals/cancellations need the Cashier webhook
        try {
            $session = Cashier::stripe()->checkout->sessions->retrieve($sessionId, ['expand' => ['line_items']]);
            if ($session->payment_status !== 'paid') {
                return;
            }

            $priceId = $session->line_items->data[0]->price->id ?? null;
            $planName = array_search($priceId, config('services.stripe.prices'), true);

            if ($planName && $this->business) {
                $this->business->update(['plan_id' => Plan::where('name', $planName)->value('id')]);
                $this->dispatch('saved');
            }
        } catch (\Throwable $e) {
            report($e);
            $this->addError('billing', 'Could not verify the checkout session.');
        }
    }

    public function render()
    {
        return view('livewire.portal.billing', [
            'plans' => Plan::orderBy('priority_rank')->get(),
            'invoices' => auth()->user()->hasStripeId()
                ? auth()->user()->invoices()
                : collect(),
        ]);
    }
}
