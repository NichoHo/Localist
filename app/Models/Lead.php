<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model
{
    protected $guarded = [];

    protected $casts = ['read_at' => 'datetime'];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
