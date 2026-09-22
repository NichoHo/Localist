<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Business extends Model
{
    protected $guarded = [];

    protected $casts = [
        'hours' => 'array',
        'featured_until' => 'datetime',
        'lat' => 'float',
        'lng' => 'float',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(Media::class)->orderBy('sort_order');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    // Featured plans rank above free, then alphabetical.
    public function scopeRanked(Builder $query): Builder
    {
        return $query
            ->join('plans', 'plans.id', '=', 'businesses.plan_id')
            ->orderByDesc('plans.priority_rank')
            ->orderBy('businesses.name')
            ->select('businesses.*');
    }

    public function isFeatured(): bool
    {
        return $this->plan->priority_rank > 0;
    }

    private const DAY_KEYS = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];

    // Stored hours are the business's own local wall-clock time; the app's clock
    // (config/app.php) is UTC, and Indonesia spans three zones (WIB/WITA/WIT),
    // so every comparison needs the business's city's own timezone, not a fixed one.
    private function localNow(): Carbon
    {
        return now($this->city->timezone ?? 'Asia/Jakarta');
    }

    public function isOpenNow(): bool
    {
        $today = $this->hours[self::DAY_KEYS[$this->localNow()->dayOfWeekIso - 1]] ?? null;
        if (! $today || ($today['closed'] ?? true)) {
            return false;
        }
        $time = $this->localNow()->format('H:i');

        return $time >= $today['open'] && $time <= $today['close'];
    }

    // null when no hours are on file at all, so callers can hide the status pill entirely.
    public function openStatusLabel(): ?string
    {
        if (! $this->hours) {
            return null;
        }

        return $this->isOpenNow() ? 'Open now' : $this->nextOpenLabel();
    }

    private function nextOpenLabel(): string
    {
        $names = ['mon' => 'Mon', 'tue' => 'Tue', 'wed' => 'Wed', 'thu' => 'Thu', 'fri' => 'Fri', 'sat' => 'Sat', 'sun' => 'Sun'];

        for ($i = 0; $i < 7; $i++) {
            $date = $this->localNow()->addDays($i);
            $key = self::DAY_KEYS[$date->dayOfWeekIso - 1];
            $slot = $this->hours[$key] ?? null;

            if (! $slot || ($slot['closed'] ?? true)) {
                continue;
            }
            if ($i === 0) {
                if ($date->format('H:i') < $slot['open']) {
                    return 'Opens '.$slot['open'].' today';
                }

                continue; // already past close today, keep looking
            }

            return 'Opens '.$slot['open'].' '.($i === 1 ? 'tomorrow' : $names[$key]);
        }

        return 'Closed';
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
