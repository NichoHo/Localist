<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $guarded = [];

    // null when the price isn't one of ours (config/services.php stripe.prices)
    public static function idForStripePrice(?string $priceId): ?int
    {
        $name = array_search($priceId, config('services.stripe.prices'), true);

        return $name ? self::where('name', $name)->value('id') : null;
    }
}
