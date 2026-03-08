<?php

namespace Ghanem\Rating\Events;

use Ghanem\Rating\Models\Rating;
use Illuminate\Foundation\Events\Dispatchable;

class RatingDeleted
{
    use Dispatchable;

    public function __construct(public Rating $rating)
    {
    }
}
