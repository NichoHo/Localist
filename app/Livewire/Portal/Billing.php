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

        $user = auth()->user();

        // Already subscribed: change the price on that subscription. A second Checkout would bill twice.
        if (($subscription = $user->subscription('default'))?->active()) {
            try {
                if ($subscription->onGracePeriod()) {
                    $subscription->resume();
                }
                $subscription->swapAndInvoice($price);
            } catch (\Throwable $e) {
                report($e);
                $this->addError('billing', 'Could not change your plan. Check your payment method and try again.');

                return;
            }

            // The webhook confirms this too; update now so the page reflects it immediately.
            $this->business->update(['plan_id' => $plan->id]);
            $this->dispatch('saved');

            return;
        }

        $session = $user
            ->newSubscription('default', $price)
            ->checkout([
                'success_url' => route('portal.billing').'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('portal.billing'),
            ]);

        // Cashier's own redirect() clashes with Livewire's redirector, so send the URL ourselves.
        return $this->redirect($session->url);
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
        // Immediate feedback on return; renewals/cancellations arrive via the webhook (SyncPlanFromSubscription).
        try {
            $session = Cashier::stripe()->checkout->sessions->retrieve($sessionId, ['expand' => ['line_items']]);
            if ($session->payment_status !== 'paid') {
                return;
            }

            $planId = Plan::idForStripePrice($session->line_items->data[0]->price->id ?? null);

            if ($planId && $this->business) {
                $this->business->update(['plan_id' => $planId]);
                $this->dispatch('saved');
            }
        } catch (\Throwable $e) {
            report($e);
            $this->addError('billing', 'Could not verify the checkout session.');
        }
    }

    public function render()
    {
        $plans = Plan::orderBy('priority_rank')->get();

        return view('livewire.portal.billing', [
            'plans' => $plans,
            'photoCount' => $this->business?->media()->count() ?? 0,
            'leadCount' => $this->business?->leads()->count() ?? 0,
            'subscription' => auth()->user()->subscription('default'),
            'testMode' => str_starts_with((string) config('cashier.key'), 'pk_test'),
            // The listing as it would read on the cheapest paid plan, for the placement preview.
            'upgradePreview' => $this->business?->plan->priority_rank === 0 && ($paid = $plans->firstWhere('priority_rank', '>', 0))
                ? $this->business->replicate()
                    ->setRelation('plan', $paid)
                    ->setRelation('category', $this->business->category)
                    ->setRelation('city', $this->business->city)
                : null,
            'invoices' => $this->invoices(),
        ]);
    }

    // A Stripe outage should hide the invoice list, not take the whole page down.
    private function invoices()
    {
        try {
            return auth()->user()->hasStripeId() ? auth()->user()->invoices() : collect();
        } catch (\Throwable $e) {
            report($e);

            return collect();
        }
    }
}
