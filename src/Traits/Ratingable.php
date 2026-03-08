<?php

namespace Ghanem\Rating\Traits;

use Ghanem\Rating\Models\Rating;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Ratingable
{
    public function ratings(): MorphMany
    {
        return $this->morphMany(Rating::class, 'ratingable');
    }

    public function avgRating(): float
    {
        return (float) $this->ratings()->avg('rating');
    }

    public function sumRating(): float
    {
        return (float) $this->ratings()->sum('rating');
    }

    public function ratingPercent(int $max = 5): float
    {
        $quantity = $this->ratings()->count();
        $total = $this->sumRating();

        return ($quantity * $max) > 0 ? $total / (($quantity * $max) / 100) : 0;
    }

    public function countPositive(): int
    {
        return $this->ratings()->where('rating', '>', 0)->count();
    }

    public function countNegative(): int
    {
        return $this->ratings()->where('rating', '<', 0)->count();
    }

    public function rating(array $data, Model $author): Rating
    {
        return (new Rating())->createRating($this, $data, $author);
    }

    public function ratingUnique(array $data, Model $author): Rating
    {
        return (new Rating())->createUniqueRating($this, $data, $author);
    }

    public function updateRating(int $id, array $data): Rating
    {
        return (new Rating())->updateRating($id, $data);
    }

    public function deleteRating(int $id): bool
    {
        return (new Rating())->deleteRating($id);
    }

    public function getAvgRatingAttribute(): float
    {
        return $this->avgRating();
    }

    public function getRatingPercentAttribute(): float
    {
        return $this->ratingPercent();
    }

    public function getSumRatingAttribute(): float
    {
        return $this->sumRating();
    }

    public function getCountPositiveAttribute(): int
    {
        return $this->countPositive();
    }

    public function getCountNegativeAttribute(): int
    {
        return $this->countNegative();
    }
}
