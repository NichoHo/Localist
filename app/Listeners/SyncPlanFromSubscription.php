<?php

namespace App\Listeners;

use App\Models\Plan;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Events\WebhookHandled;

// Cashier keeps the subscriptions table in sync from Stripe; this mirrors that onto businesses.plan_id.
class SyncPlanFromSubscription
{
    public function handle(WebhookHandled $event): void
    {
        if (! str_starts_with($event->payload['type'], 'customer.subscription.')) {
            return;
        }

        $user = Cashier::findBillable($event->payload['data']['object']['customer']);
        if (! $business = $user?->business()) {
            return;
        }

        $subscription = $user->subscription('default');
        $planId = $subscription?->valid() ? Plan::idForStripePrice($subscription->stripe_price) : null;

        $business->update(['plan_id' => $planId ?? Plan::where('name', 'Free')->value('id')]);
    }
}
