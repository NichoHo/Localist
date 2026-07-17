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
        $this->featured = Plan::create(['name' => 'Featured', 'price_monthly' => 49, 'priority_rank' => 1]);
        $category = Category::create(['name' => 'Plumbers', 'slug' => 'plumbers']);
        $city = City::create(['name' => 'Kuala Lumpur', 'slug' => 'kuala-lumpur']);
        $this->owner = User::factory()->create();
        $this->business = Business::create([
            'name' => 'Zzz Plumbing', 'slug' => 'zzz-plumbing',
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

    public function test_downgrade_to_free_changes_plan_and_ranking(): void
    {
        Business::create([
            'name' => 'Aaa Plumbing', 'slug' => 'aaa-plumbing',
            'category_id' => $this->business->category_id, 'city_id' => $this->business->city_id,
            'plan_id' => $this->free->id, 'status' => 'published',
        ]);

        // Featured "Zzz" outranks free "Aaa" before the downgrade...
        $before = $this->get('/kuala-lumpur/plumbers')->getContent();
        $this->assertTrue(strpos($before, 'Zzz Plumbing') < strpos($before, 'Aaa Plumbing'));

        Livewire::actingAs($this->owner)->test(Billing::class)
            ->call('downgradeToFree')
            ->assertDispatched('saved');

        $this->assertEquals($this->free->id, $this->business->fresh()->plan_id);

        // ...and drops below it after: plan change changes ranking.
        $after = $this->get('/kuala-lumpur/plumbers')->getContent();
        $this->assertTrue(strpos($after, 'Aaa Plumbing') < strpos($after, 'Zzz Plumbing'));
    }
}
