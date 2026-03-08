<?php

namespace Ghanem\Rating\Events;

use Ghanem\Rating\Models\Rating;
use Illuminate\Foundation\Events\Dispatchable;

class RatingUpdated
{
    use Dispatchable;

    public function __construct(public Rating $rating)
    {
    }
}
