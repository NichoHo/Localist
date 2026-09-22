<?php

namespace Tests\Feature;

use App\Livewire\Portal\Billing;
use App\Models\Business;
use App\Models\Category;
use App\Models\City;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BillingTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Business $business;

    private Plan $free;

    private Plan $featured;

    protected function setUp(): void
    {
        parent::setUp();

        $this->free = Plan::create(['name' => 'Free', 'price_monthly' => 0, 'priority_rank' => 0]);
        $this->featured = Plan::create(['name' => 'Featured', 'price_monthly' => 149000, 'priority_rank' => 1]);
        $category = Category::create(['name' => 'Cafes & Coffee Shops', 'slug' => 'cafes-coffee']);
        $city = City::create(['name' => 'Jakarta', 'slug' => 'jakarta']);
        $this->owner = User::factory()->create();
        $this->business = Business::create([
            'name' => 'Zzz Coffee', 'slug' => 'zzz-coffee',
            'category_id' => $category->id, 'city_id' => $city->id,
            'plan_id' => $this->featured->id, 'status' => 'published', 'user_id' => $this->owner->id,
        ]);
    }

    public function test_billing_page_shows_plans_and_current_plan(): void
    {
        $this->actingAs($this->owner)->get('/billing')
            ->assertOk()
            ->assertSee('Current plan')
            ->assertSee('Featured');
    }

    public function test_checkout_without_stripe_config_shows_friendly_error(): void
    {
        config(['cashier.secret' => null, 'services.stripe.prices.Featured' => null]);

        Livewire::actingAs($this->owner)->test(Billing::class)
            ->call('checkout', $this->featured->id)
            ->assertHasErrors('billing');
    }

    public function test_stripe_webhook_syncs_plan_from_subscription(): void
    {
        config(['services.stripe.prices.Featured' => 'price_featured', 'cashier.webhook.secret' => null]);
        $this->owner->forceFill(['stripe_id' => 'cus_123'])->save();
        $this->business->update(['plan_id' => $this->free->id]);

        $payload = fn (string $type, string $status) => ['type' => $type, 'data' => ['object' => [
            'id' => 'sub_123', 'customer' => 'cus_123', 'status' => $status, 'cancel_at_period_end' => false,
            'items' => ['data' => [['id' => 'si_1', 'price' => ['id' => 'price_featured', 'product' => 'prod_1'], 'quantity' => 1]]],
        ]]];

        // Subscription created in Stripe (checkout, or manually in the dashboard) → paid plan.
        $this->postJson('/stripe/webhook', $payload('customer.subscription.updated', 'active'))->assertOk();
        $this->assertEquals($this->featured->id, $this->business->fresh()->plan_id);

        // Cancelled or expired in Stripe → back to Free without anyone visiting the portal.
        $this->postJson('/stripe/webhook', $payload('customer.subscription.deleted', 'canceled'))->assertOk();
        $this->assertEquals($this->free->id, $this->business->fresh()->plan_id);
    }

    public function test_downgrade_to_free_changes_plan_and_ranking(): void
    {
        Business::create([
            'name' => 'Aaa Coffee', 'slug' => 'aaa-coffee',
            'category_id' => $this->business->category_id, 'city_id' => $this->business->city_id,
            'plan_id' => $this->free->id, 'status' => 'published',
        ]);

        // Featured "Zzz" outranks free "Aaa" before the downgrade...
        $before = $this->get('/jakarta/cafes-coffee')->getContent();
        $this->assertTrue(strpos($before, 'Zzz Coffee') < strpos($before, 'Aaa Coffee'));

        Livewire::actingAs($this->owner)->test(Billing::class)
            ->call('downgradeToFree')
            ->assertDispatched('saved');

        $this->assertEquals($this->free->id, $this->business->fresh()->plan_id);

        // ...and drops below it after: plan change changes ranking.
        $after = $this->get('/jakarta/cafes-coffee')->getContent();
        $this->assertTrue(strpos($after, 'Aaa Coffee') < strpos($after, 'Zzz Coffee'));
    }
}
