<?php

namespace App\Services;

use App\Models\Business;
use Illuminate\Support\Collection;

class Seo
{
    public static function localBusiness(Business $business): array
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => $business->name,
            'description' => $business->description,
            'url' => route('business', $business),
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $business->address,
                'addressLocality' => $business->city->name,
                'addressRegion' => $business->city->region,
                'addressCountry' => 'MY',
            ],
        ];

        if ($business->lat && $business->lng) {
            $data['geo'] = [
                '@type' => 'GeoCoordinates',
                'latitude' => $business->lat,
                'longitude' => $business->lng,
            ];
        }

        if ($business->phone) {
            $data['telephone'] = $business->phone;
        }

        if ($business->relationLoaded('media') && $business->media->isNotEmpty()) {
            $data['image'] = $business->media->map(fn ($m) => asset('storage/'.$m->path))->all();
        }

        if ($business->hours) {
            $dayMap = ['mon' => 'Monday', 'tue' => 'Tuesday', 'wed' => 'Wednesday', 'thu' => 'Thursday', 'fri' => 'Friday', 'sat' => 'Saturday', 'sun' => 'Sunday'];
            $data['openingHoursSpecification'] = collect($business->hours)
                ->reject(fn ($slot) => $slot['closed'] ?? false)
                ->map(fn ($slot, $day) => [
                    '@type' => 'OpeningHoursSpecification',
                    'dayOfWeek' => $dayMap[$day] ?? $day,
                    'opens' => $slot['open'] ?? null,
                    'closes' => $slot['close'] ?? null,
                ])->values()->all();
        }

        return $data;
    }

    /** @param array<array{0: string, 1: string}> $crumbs [[name, url], ...] */
    public static function breadcrumbs(array $crumbs): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($crumbs)->map(fn ($crumb, $i) => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $crumb[0],
                'item' => $crumb[1],
            ])->all(),
        ];
    }

    /** @param Collection<int, Business> $businesses */
    public static function itemList(Collection $businesses, string $name): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => $name,
            'itemListElement' => $businesses->values()->map(fn ($business, $i) => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $business->name,
                'url' => route('business', $business),
            ])->all(),
        ];
    }
}
