<?php

namespace Ghanem\Rating\Models;

use Ghanem\Rating\Events\RatingCreated;
use Ghanem\Rating\Events\RatingDeleted;
use Ghanem\Rating\Events\RatingUpdated;
use Ghanem\Rating\Exceptions\InvalidRatingException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Rating extends Model
{
    protected $table = 'ratings';

    protected $fillable = ['rating', 'body', 'type', 'weight', 'ratingable_id', 'ratingable_type', 'author_id', 'author_type'];

    protected $casts = [
        'rating' => 'float',
        'weight' => 'float',
    ];

    public function ratingable(): MorphTo
    {
        return $this->morphTo();
    }

    public function author(): MorphTo
    {
        return $this->morphTo('author');
    }

    public function createRating(Model $ratingable, array $data, Model $author): static
    {
        $this->validateRating($data['rating'] ?? 0);

        $rating = new static();
        $rating->fill(array_merge($data, [
            'author_id' => $author->id,
            'author_type' => get_class($author),
        ]));

        $ratingable->ratings()->save($rating);

        RatingCreated::dispatch($rating);

        return $rating;
    }

    public function createUniqueRating(Model $ratingable, array $data, Model $author): static
    {
        $this->validateRating($data['rating'] ?? 0);

        $rating = static::updateOrCreate(
            [
                'author_id' => $author->id,
                'author_type' => get_class($author),
                'ratingable_id' => $ratingable->id,
                'ratingable_type' => get_class($ratingable),
            ],
            $data
        );

        if ($rating->wasRecentlyCreated) {
            RatingCreated::dispatch($rating);
        } else {
            RatingUpdated::dispatch($rating);
        }

        return $rating;
    }

    public function updateRating(int $id, array $data): static
    {
        if (isset($data['rating'])) {
            $this->validateRating($data['rating']);
        }

        $rating = static::findOrFail($id);
        $rating->update($data);

        RatingUpdated::dispatch($rating);

        return $rating;
    }

    public function deleteRating(int $id): bool
    {
        $rating = static::findOrFail($id);

        RatingDeleted::dispatch($rating);

        return $rating->delete();
    }

    protected function validateRating(int|float $value): void
    {
        $config = config('rating', []);

        $allowNegative = $config['allow_negative'] ?? true;
        if (!$allowNegative && $value < 0) {
            throw InvalidRatingException::negativeNotAllowed($value);
        }

        $min = $config['min'] ?? null;
        $max = $config['max'] ?? null;

        if ($min !== null && $value < $min) {
            throw InvalidRatingException::outOfRange($value, $min, $max);
        }

        if ($max !== null && $value > $max) {
            throw InvalidRatingException::outOfRange($value, $min, $max);
        }
    }
}
