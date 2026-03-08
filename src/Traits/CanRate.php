<?php

namespace Ghanem\Rating\Traits;

use Ghanem\Rating\Models\Rating;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait CanRate
{
    public function ratings(): MorphMany
    {
        return $this->morphMany(Rating::class, 'author');
    }

    public function rate(Model $ratingable, array $data): Rating
    {
        return (new Rating())->createRating($ratingable, $data, $this);
    }

    public function rateUnique(Model $ratingable, array $data): Rating
    {
        return (new Rating())->createUniqueRating($ratingable, $data, $this);
    }

    public function hasRated(Model $ratingable): bool
    {
        return $this->ratings()
            ->where('ratingable_id', $ratingable->id)
            ->where('ratingable_type', get_class($ratingable))
            ->exists();
    }

    public function getRating(Model $ratingable): ?Rating
    {
        return $this->ratings()
            ->where('ratingable_id', $ratingable->id)
            ->where('ratingable_type', get_class($ratingable))
            ->first();
    }

    public function averageGivenRating(): float
    {
        return (float) $this->ratings()->avg('rating');
    }

    public function totalGivenRatings(): int
    {
        return $this->ratings()->count();
    }
}
