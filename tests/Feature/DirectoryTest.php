<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Category;
use App\Models\City;
use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DirectoryTest extends TestCase
{
    use RefreshDatabase;

    private function seedDirectory(): array
    {
        $free = Plan::create(['name' => 'Free', 'priority_rank' => 0]);
        $featured = Plan::create(['name' => 'Featured', 'priority_rank' => 1, 'allows_website' => true]);
        $category = Category::create(['name' => 'Cafes & Coffee Shops', 'slug' => 'cafes-coffee']);
        $city = City::create(['name' => 'Jakarta', 'slug' => 'jakarta', 'region' => 'DKI Jakarta']);

        $freeBiz = Business::create([
            'name' => 'Aaa Coffee', 'slug' => 'aaa-coffee', 'category_id' => $category->id,
            'city_id' => $city->id, 'plan_id' => $free->id, 'status' => 'published',
        ]);
        $featuredBiz = Business::create([
            'name' => 'Zzz Coffee', 'slug' => 'zzz-coffee', 'category_id' => $category->id,
            'city_id' => $city->id, 'plan_id' => $featured->id, 'status' => 'published',
        ]);

        return [$city, $category, $freeBiz, $featuredBiz];
    }

    public function test_public_pages_render(): void
    {
        [$city, $category, $freeBiz] = $this->seedDirectory();

        $this->get('/')->assertOk();
        $this->get("/category/{$category->slug}")->assertOk();
        $this->get("/{$city->slug}")->assertOk();
        $this->get("/{$city->slug}/{$category->slug}")->assertOk();
        $this->get("/business/{$freeBiz->slug}")->assertOk()->assertSee('Aaa Coffee');
        $this->get('/search?q=coffee')->assertOk()->assertSee('noindex', false);
    }

    public function test_featured_listings_rank_above_free(): void
    {
        [$city, $category] = $this->seedDirectory();

        $response = $this->get("/{$city->slug}/{$category->slug}")->assertOk();

        // Featured "Zzz" must appear before free "Aaa" despite alphabetical order.
        $this->assertTrue(
            strpos($response->getContent(), 'Zzz Coffee') < strpos($response->getContent(), 'Aaa Coffee')
        );
    }

    public function test_enquiry_creates_lead(): void
    {
        [, , $freeBiz] = $this->seedDirectory();

        $this->post("/business/{$freeBiz->slug}/enquire", [
            'name' => 'Test Visitor',
            'email' => 'visitor@example.com',
            'message' => 'Need a table for two booked.',
        ])->assertRedirect()->assertSessionHas('enquiry_sent');

        $this->assertDatabaseHas('leads', [
            'business_id' => $freeBiz->id,
            'email' => 'visitor@example.com',
        ]);
    }

    public function test_enquiry_ignores_honeypot(): void
    {
        [, , $freeBiz] = $this->seedDirectory();
        $data = ['name' => 'Visitor', 'email' => 'v@example.com', 'message' => 'Table for two.'];

        // Bot filled the hidden field: pretend success, store nothing.
        $this->post("/business/{$freeBiz->slug}/enquire", $data + ['website' => 'http://spam'])
            ->assertRedirect()->assertSessionHas('enquiry_sent');
        $this->assertDatabaseCount('leads', 0);
    }

    public function test_enquiry_is_throttled(): void
    {
        [, , $freeBiz] = $this->seedDirectory();
        $data = ['name' => 'Visitor', 'email' => 'v@example.com', 'message' => 'Hi'];

        foreach (range(1, 5) as $i) {
            $this->post("/business/{$freeBiz->slug}/enquire", $data)->assertRedirect();
        }
        $this->post("/business/{$freeBiz->slug}/enquire", $data)->assertStatus(429);
    }

    public function test_views_count_via_beacon_not_page_load(): void
    {
        [, , $freeBiz] = $this->seedDirectory();

        // Page loads are Cloudflare-cached, so they must not be the counter.
        $this->get("/business/{$freeBiz->slug}")->assertOk()->assertSee(route('business.view', $freeBiz));
        $this->assertEquals(0, $freeBiz->fresh()->views_count);

        // The beacon POST is never cached and carries no CSRF token.
        $this->post("/business/{$freeBiz->slug}/view")->assertNoContent();
        $this->assertEquals(1, $freeBiz->fresh()->views_count);
    }

    public function test_unknown_city_or_combo_is_404(): void
    {
        $this->seedDirectory();

        $this->get('/no-such-city')->assertNotFound();
        $this->get('/jakarta/no-such-category')->assertNotFound();
    }
}
