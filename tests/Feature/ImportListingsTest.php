<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportListingsTest extends TestCase
{
    use RefreshDatabase;

    private function importCsv(array $rows): void
    {
        Plan::firstOrCreate(['name' => 'Free'], ['priority_rank' => 0]);
        Plan::firstOrCreate(['name' => 'Featured'], ['priority_rank' => 1]);
        Plan::firstOrCreate(['name' => 'Premium'], ['priority_rank' => 2]);

        $path = tempnam(sys_get_temp_dir(), 'listings').'.csv';
        $handle = fopen($path, 'w');
        fputcsv($handle, ['name', 'category', 'city', 'region', 'address', 'phone', 'website', 'email', 'lat', 'lng', 'timezone']);
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        fclose($handle);

        $this->artisan('listings:import', ['file' => $path])->assertSuccessful();
        unlink($path);
    }

    /** Real chains repeat the same name across cities; slug is globally unique. */
    public function test_same_name_in_different_cities_gets_distinct_slugs(): void
    {
        $this->importCsv([
            ['Shop & Drive', 'Auto Repair & Services', 'Jakarta', 'DKI Jakarta', 'Jl. A', '021', '', '', -6.2, 106.8, 'Asia/Jakarta'],
            ['Shop & Drive', 'Auto Repair & Services', 'Bandung', 'Jawa Barat', 'Jl. B', '022', '', '', -6.9, 107.6, 'Asia/Jakarta'],
        ]);

        $slugs = Business::pluck('slug')->all();
        $this->assertCount(2, array_unique($slugs));
        $this->assertContains('shop-drive', $slugs);
        $this->assertContains('shop-drive-bandung', $slugs);
    }

    /** A non-Latin-script name (e.g. Japanese katakana) slugs to '' via Str::slug(). */
    public function test_unslugable_name_falls_back_to_category(): void
    {
        $this->importCsv([
            ['ザ・ルキシオ・ホテル', 'Hotels & Stays', 'Sorong', 'Papua Barat Daya', 'Jl. C', '0951', '', '', -0.87, 131.25, 'Asia/Jayapura'],
        ]);

        $slug = Business::first()->slug;
        $this->assertNotSame('', $slug);
        $this->assertStringStartsWith('hotels-stays', $slug);
    }

    /** Re-running the same file must not fail on the slug-uniqueness check against itself. */
    public function test_reimporting_the_same_file_is_idempotent(): void
    {
        $row = ['Sunrise Coffee', 'Cafes & Coffee Shops', 'Jakarta', 'DKI Jakarta', 'Jl. A', '021', '', '', -6.2, 106.8, 'Asia/Jakarta'];
        $this->importCsv([$row]);
        $this->importCsv([$row]);

        $this->assertSame(1, Business::count());
    }
}
