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
                ['slug' => $data['category_slug'] ?? Str::slug($data['category'])],
                ['name' => $data['category']]
            )->id;

            $cities[$data['city']] ??= City::firstOrCreate(
                ['slug' => Str::slug($data['city'])],
                [
                    'name' => $data['city'], 'region' => $data['region'] ?? null,
                    'lat' => $data['lat'] ?? null, 'lng' => $data['lng'] ?? null,
                    'timezone' => $data['timezone'] ?? 'Asia/Jakarta',
                ]
            )->id;

            $cityId = $cities[$data['city']];

            // Deterministic demo tiering: ~8% Featured, ~2% Premium.
            $bucket = crc32($data['name']) % 100;
            $plan = $bucket < 2 ? 'Premium' : ($bucket < 10 ? 'Featured' : 'Free');

            // slug is globally unique, but real chains (e.g. "Shop & Drive") repeat the
            // same name across many cities, and open POI data has near-duplicate rows
            // (slightly different punctuation) for the same place in the same city.
            // Escalate: plain name -> +city -> +city+counter, until it's free.
            // A non-Latin-script name (e.g. Japanese katakana) slugs to '' — fall back
            // to the category so the slug is never empty.
            $base = Str::slug($data['name']) ?: Str::slug($data['category']);
            $slug = $base;
            for ($n = 2; Business::where('slug', $slug)
                ->where(fn ($q) => $q->where('name', '!=', $data['name'])->orWhere('city_id', '!=', $cityId))
                ->exists(); $n++) {
                $slug = $base.'-'.($n === 2 ? Str::slug($data['city']) : $n);
            }

            $business = Business::updateOrCreate(
                ['name' => $data['name'], 'city_id' => $cityId],
                [
                    'slug' => $slug,
                    'category_id' => $categories[$data['category']],
                    'description' => $data['description'] ?? null,
                    'address' => $data['address'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'website' => $data['website'] ?: null,
                    'email' => $data['email'] ?? null,
                    'hours' => isset($data['hours']) ? json_decode($data['hours'], true) : null,
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
