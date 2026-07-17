<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
