<?php

namespace Ghanem\Rating\Traits;

use Ghanem\Rating\Models\Rating;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Ratingable
{
    public function ratings(): MorphMany
    {
        return $this->morphMany(Rating::class, 'ratingable');
    }

    public function avgRating(?string $type = null): float
    {
        return (float) $this->ratingsQuery($type)->avg('rating');
    }

    public function sumRating(?string $type = null): float
    {
        return (float) $this->ratingsQuery($type)->sum('rating');
    }

    public function ratingPercent(int $max = 5, ?string $type = null): float
    {
        $query = $this->ratingsQuery($type);
        $quantity = $query->count();
        $total = (float) $query->sum('rating');

        return ($quantity * $max) > 0 ? $total / (($quantity * $max) / 100) : 0;
    }

    public function countPositive(?string $type = null): int
    {
        return $this->ratingsQuery($type)->where('rating', '>', 0)->count();
    }

    public function countNegative(?string $type = null): int
    {
        return $this->ratingsQuery($type)->where('rating', '<', 0)->count();
    }

    public function countRatings(?string $type = null): int
    {
        return $this->ratingsQuery($type)->count();
    }

    public function weightedAvgRating(?string $type = null): float
    {
        $query = $this->ratingsQuery($type);

        $totalWeight = (float) $query->sum('weight');
        if ($totalWeight <= 0) {
            return $this->avgRating($type);
        }

        return (float) $query->selectRaw('SUM(rating * COALESCE(weight, 1)) / SUM(COALESCE(weight, 1)) as weighted_avg')
            ->value('weighted_avg');
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

    public function isRatedBy(Model $author, ?string $type = null): bool
    {
        $query = $this->ratings()
            ->where('author_id', $author->id)
            ->where('author_type', get_class($author));

        if ($type !== null) {
            $query->where('type', $type);
        }

        return $query->exists();
    }

    // -- Query Scopes --

    public function scopeWithAvgRating(Builder $query, ?string $type = null): Builder
    {
        return $query->withAvg(
            ['ratings' => fn ($q) => $type ? $q->where('type', $type) : $q],
            'rating'
        );
    }

    public function scopeWithSumRating(Builder $query, ?string $type = null): Builder
    {
        return $query->withSum(
            ['ratings' => fn ($q) => $type ? $q->where('type', $type) : $q],
            'rating'
        );
    }

    public function scopeWithCountRatings(Builder $query, ?string $type = null): Builder
    {
        return $query->withCount(
            ['ratings' => fn ($q) => $type ? $q->where('type', $type) : $q]
        );
    }

    public function scopeOrderByAvgRating(Builder $query, string $direction = 'desc', ?string $type = null): Builder
    {
        return $query->withAvgRating($type)->orderBy('ratings_avg_rating', $direction);
    }

    public function scopeOrderBySumRating(Builder $query, string $direction = 'desc', ?string $type = null): Builder
    {
        return $query->withSumRating($type)->orderBy('ratings_sum_rating', $direction);
    }

    public function scopeOrderByCountRatings(Builder $query, string $direction = 'desc', ?string $type = null): Builder
    {
        return $query->withCountRatings($type)->orderBy('ratings_count', $direction);
    }

    public function scopeMinAvgRating(Builder $query, float $min, ?string $type = null): Builder
    {
        return $query->withAvgRating($type)->having('ratings_avg_rating', '>=', $min);
    }

    public function scopeMinSumRating(Builder $query, float $min, ?string $type = null): Builder
    {
        return $query->withSumRating($type)->having('ratings_sum_rating', '>=', $min);
    }

    // -- Attributes --

    protected function ratingsQuery(?string $type = null): MorphMany
    {
        $query = $this->ratings();

        if ($type !== null) {
            $query->where('type', $type);
        }

        return $query;
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

    public function getCountRatingsAttribute(): int
    {
        return $this->countRatings();
    }

    public function getWeightedAvgRatingAttribute(): float
    {
        return $this->weightedAvgRating();
    }
}
