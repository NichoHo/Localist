<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Category;
use App\Models\City;
use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();

        $plan = Plan::create(['name' => 'Free', 'priority_rank' => 0]);
        $category = Category::create(['name' => 'Cafes & Coffee Shops', 'slug' => 'cafes-coffee']);
        $city = City::create(['name' => 'Jakarta', 'slug' => 'jakarta', 'region' => 'DKI Jakarta']);
        $this->business = Business::create([
            'name' => 'Sunrise Coffee', 'slug' => 'sunrise-coffee', 'description' => 'Fresh coffee, brewed fast.',
            'address' => 'Jl. Test No. 1', 'phone' => '0812-3456-7890', 'lat' => -6.2, 'lng' => 106.8,
            'hours' => ['mon' => ['open' => '09:00', 'close' => '18:00', 'closed' => false], 'sun' => ['closed' => true]],
            'category_id' => $category->id, 'city_id' => $city->id, 'plan_id' => $plan->id, 'status' => 'published',
        ]);
    }

    /** Extract all JSON-LD blocks from a page and decode them. */
    private function jsonLd(string $html): array
    {
        preg_match_all('/<script type="application\/ld\+json">(.+?)<\/script>/s', $html, $matches);

        return array_map(function ($json) {
            $decoded = json_decode($json, true);
            $this->assertNotNull($decoded, 'JSON-LD block is not valid JSON: '.$json);

            return $decoded;
        }, $matches[1]);
    }

    public function test_business_page_has_valid_local_business_and_breadcrumbs(): void
    {
        $html = $this->get('/business/sunrise-coffee')->assertOk()->getContent();
        $blocks = collect($this->jsonLd($html))->keyBy('@type');

        $lb = $blocks['LocalBusiness'];
        $this->assertEquals('Sunrise Coffee', $lb['name']);
        $this->assertEquals('Jakarta', $lb['address']['addressLocality']);
        $this->assertEquals(-6.2, $lb['geo']['latitude']);
        // Closed days excluded, open days present.
        $this->assertCount(1, $lb['openingHoursSpecification']);
        $this->assertEquals('Monday', $lb['openingHoursSpecification'][0]['dayOfWeek']);

        $crumbs = $blocks['BreadcrumbList']['itemListElement'];
        $this->assertCount(4, $crumbs);
        $this->assertEquals(['Home', 'Jakarta', 'Cafes & Coffee Shops', 'Sunrise Coffee'], array_column($crumbs, 'name'));
        $this->assertEquals([1, 2, 3, 4], array_column($crumbs, 'position'));
    }

    public function test_city_category_page_has_item_list_and_breadcrumbs(): void
    {
        $html = $this->get('/jakarta/cafes-coffee')->assertOk()->getContent();
        $blocks = collect($this->jsonLd($html))->keyBy('@type');

        $this->assertEquals('Sunrise Coffee', $blocks['ItemList']['itemListElement'][0]['name']);
        $this->assertArrayHasKey('BreadcrumbList', $blocks);
    }

    public function test_public_pages_have_self_canonical_and_search_is_noindex(): void
    {
        $this->get('/business/sunrise-coffee')
            ->assertSee('<link rel="canonical" href="'.url('/business/sunrise-coffee').'">', false);
        $this->get('/search?q=x')->assertSee('<meta name="robots" content="noindex">', false);
    }

    public function test_sitemap_generation_chunks_and_indexes(): void
    {
        $dir = sys_get_temp_dir().'/sitemap-test-'.uniqid();
        mkdir($dir);

        // 1 business + max=1 per file; cities section has 1 city page + 1 combo page → 2 files.
        $this->artisan('sitemap:generate', ['--max' => 1, '--path' => $dir])->assertSuccessful();

        $index = simplexml_load_file($dir.'/sitemap.xml');
        $this->assertNotFalse($index);
        $this->assertCount(4, $index->sitemap); // businesses, categories, cities, cities-2

        $businesses = simplexml_load_file($dir.'/sitemap-businesses.xml');
        $this->assertEquals(url('/business/sunrise-coffee'), (string) $businesses->url[0]->loc);

        array_map('unlink', glob($dir.'/*.xml'));
        rmdir($dir);
    }
}
