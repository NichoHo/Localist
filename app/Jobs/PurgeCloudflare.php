<?php

namespace App\Jobs;

use App\Models\Business;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PurgeCloudflare implements ShouldQueue
{
    use Queueable;

    /** @param string[] $urls */
    public function __construct(public array $urls) {}

    /** Everything a listing edit makes stale: the listing page and its index pages. */
    public static function forBusiness(Business $business, string ...$extraUrls): self
    {
        $business->loadMissing(['city', 'category']);

        return new self(array_values(array_unique([
            route('business', $business),
            route('city.category', [$business->city, $business->category]),
            route('category', $business->category),
            route('city', $business->city),
            route('home'),
            ...$extraUrls,
        ])));
    }

    public function handle(): void
    {
        $zone = config('services.cloudflare.zone_id');
        $token = config('services.cloudflare.api_token');

        if (! $zone || ! $token) {
            Log::debug('PurgeCloudflare skipped: zone/token not configured', ['urls' => $this->urls]);

            return;
        }

        Http::withToken($token)
            ->post("https://api.cloudflare.com/client/v4/zones/{$zone}/purge_cache", ['files' => $this->urls])
            ->throw();
    }
}
