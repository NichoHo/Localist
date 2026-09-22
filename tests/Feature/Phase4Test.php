<?php

namespace Tests\Feature;

use App\Jobs\PurgeCloudflare;
use App\Livewire\Admin\Listings;
use App\Livewire\Portal\EditListing;
use App\Models\Business;
use App\Models\Category;
use App\Models\City;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class Phase4Test extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();

        $plan = Plan::create(['name' => 'Free', 'priority_rank' => 0]);
        $category = Category::create(['name' => 'Cafes & Coffee Shops', 'slug' => 'cafes-coffee']);
        $city = City::create(['name' => 'Jakarta', 'slug' => 'jakarta']);
        $this->owner = User::factory()->create();
        $this->business = Business::create([
            'name' => 'Sunrise Coffee', 'slug' => 'sunrise-coffee', 'description' => 'Fresh coffee, brewed fast.',
            'category_id' => $category->id, 'city_id' => $city->id, 'plan_id' => $plan->id,
            'status' => 'published', 'user_id' => $this->owner->id,
        ]);
    }

    public function test_public_pages_are_cacheable_and_portal_is_not(): void
    {
        $public = $this->get('/business/sunrise-coffee')->assertOk();
        $this->assertStringContainsString('public', $public->headers->get('Cache-Control'));
        $this->assertStringContainsString('max-age=600', $public->headers->get('Cache-Control'));
        $this->assertNotNull($public->headers->get('ETag'));

        $portal = $this->actingAs($this->owner)->get('/dashboard')->assertOk();
        $this->assertStringNotContainsString('public', $portal->headers->get('Cache-Control'));
    }

    public function test_editing_a_listing_dispatches_cache_purge_with_its_urls(): void
    {
        Queue::fake();

        Livewire::actingAs($this->owner)->test(EditListing::class)
            ->set('description', 'Fresh description.')
            ->call('save')
            ->assertHasNoErrors();

        Queue::assertPushed(PurgeCloudflare::class, function (PurgeCloudflare $job) {
            return in_array(route('business', 'sunrise-coffee'), $job->urls)
                && in_array(url('jakarta/cafes-coffee'), $job->urls);
        });
    }

    public function test_purge_job_noops_without_cloudflare_config(): void
    {
        config(['services.cloudflare.zone_id' => null, 'services.cloudflare.api_token' => null]);

        // Would throw if it tried to hit the API (no HTTP fake registered).
        (new PurgeCloudflare(['http://localhost/x']))->handle();
        $this->assertTrue(true);
    }

    public function test_admin_can_approve_pending_listing_making_it_public(): void
    {
        Queue::fake();
        $this->business->update(['status' => 'pending']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->get('/business/sunrise-coffee')->assertNotFound();

        Livewire::actingAs($admin)->test(Listings::class)
            ->assertSee('Sunrise Coffee')
            ->call('approve', $this->business->id);

        $this->assertEquals('published', $this->business->fresh()->status);
        $this->get('/business/sunrise-coffee')->assertOk();
        Queue::assertPushed(PurgeCloudflare::class);
    }

    public function test_admin_page_is_forbidden_for_regular_owners(): void
    {
        $this->get('/admin')->assertRedirect('/login');
        $this->actingAs($this->owner)->get('/admin')->assertForbidden();
    }

    public function test_pulse_dashboard_is_admin_only(): void
    {
        $this->get('/pulse')->assertForbidden();
        $this->actingAs($this->owner)->get('/pulse')->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get('/pulse')->assertOk();
    }
}
