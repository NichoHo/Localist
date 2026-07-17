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
        $category = Category::create(['name' => 'Plumbers', 'slug' => 'plumbers']);
        $city = City::create(['name' => 'Kuala Lumpur', 'slug' => 'kuala-lumpur', 'region' => 'Federal Territory']);

        $freeBiz = Business::create([
            'name' => 'Aaa Plumbing', 'slug' => 'aaa-plumbing', 'category_id' => $category->id,
            'city_id' => $city->id, 'plan_id' => $free->id, 'status' => 'published',
        ]);
        $featuredBiz = Business::create([
            'name' => 'Zzz Plumbing', 'slug' => 'zzz-plumbing', 'category_id' => $category->id,
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
        $this->get("/business/{$freeBiz->slug}")->assertOk()->assertSee('Aaa Plumbing');
        $this->get('/search?q=plumbing')->assertOk()->assertSee('noindex', false);
    }

    public function test_featured_listings_rank_above_free(): void
    {
        [$city, $category] = $this->seedDirectory();

        $response = $this->get("/{$city->slug}/{$category->slug}")->assertOk();

        // Featured "Zzz" must appear before free "Aaa" despite alphabetical order.
        $this->assertTrue(
            strpos($response->getContent(), 'Zzz Plumbing') < strpos($response->getContent(), 'Aaa Plumbing')
        );
    }

    public function test_enquiry_creates_lead(): void
    {
        [, , $freeBiz] = $this->seedDirectory();

        $this->post("/business/{$freeBiz->slug}/enquire", [
            'name' => 'Test Visitor',
            'email' => 'visitor@example.com',
            'message' => 'Need a leaking pipe fixed.',
        ])->assertRedirect()->assertSessionHas('enquiry_sent');

        $this->assertDatabaseHas('leads', [
            'business_id' => $freeBiz->id,
            'email' => 'visitor@example.com',
        ]);
    }

    public function test_unknown_city_or_combo_is_404(): void
    {
        $this->seedDirectory();

        $this->get('/no-such-city')->assertNotFound();
        $this->get('/kuala-lumpur/no-such-category')->assertNotFound();
    }
}
