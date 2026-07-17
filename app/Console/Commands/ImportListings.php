<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Models\Category;
use App\Models\City;
use App\Models\Plan;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportListings extends Command
{
    protected $signature = 'listings:import {file : Path to CSV file}';

    protected $description = 'Bulk-import business listings from a CSV. Idempotent: re-running updates rows matched by name + city.';

    public function handle(): int
    {
        $file = $this->argument('file');
        if (! is_readable($file)) {
            $this->error("Cannot read file: $file");

            return self::FAILURE;
        }

        $handle = fopen($file, 'r');
        $header = fgetcsv($handle);
        $plans = Plan::pluck('id', 'name');
        $categories = [];
        $cities = [];
        $created = 0;
        $updated = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($header, $row);

            $categories[$data['category']] ??= Category::firstOrCreate(
                ['slug' => Str::slug($data['category'])],
                ['name' => $data['category']]
            )->id;

            $cities[$data['city']] ??= City::firstOrCreate(
                ['slug' => Str::slug($data['city'])],
                ['name' => $data['city'], 'region' => $data['region'] ?? null, 'lat' => $data['lat'] ?? null, 'lng' => $data['lng'] ?? null]
            )->id;

            $cityId = $cities[$data['city']];

            // Deterministic demo tiering: ~8% Featured, ~2% Premium.
            $bucket = crc32($data['name']) % 100;
            $plan = $bucket < 2 ? 'Premium' : ($bucket < 10 ? 'Featured' : 'Free');

            $business = Business::updateOrCreate(
                ['name' => $data['name'], 'city_id' => $cityId],
                [
                    'slug' => Str::slug($data['name']),
                    'category_id' => $categories[$data['category']],
                    'description' => $data['description'] ?? null,
                    'address' => $data['address'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'website' => $data['website'] ?: null,
                    'email' => $data['email'] ?? null,
                    'lat' => $data['lat'] ?? null,
                    'lng' => $data['lng'] ?? null,
                    'plan_id' => $plans[$plan],
                    'status' => 'published',
                ]
            );

            $business->wasRecentlyCreated ? $created++ : $updated++;

            if (($created + $updated) % 500 === 0) {
                $this->info(($created + $updated).' rows processed...');
            }
        }
        fclose($handle);

        // ponytail: rows without lat/lng would need the geocode job (Phase 1 seed CSV ships coordinates, so none do)
        $this->info("Done. Created: $created, updated: $updated.");
        $this->call('sitemap:generate');

        return self::SUCCESS;
    }
}
