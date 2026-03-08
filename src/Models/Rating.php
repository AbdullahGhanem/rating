<?php

namespace Ghanem\Rating\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Rating extends Model
{
    protected $table = 'ratings';

    protected $fillable = ['rating', 'ratingable_id', 'ratingable_type', 'author_id', 'author_type'];

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
        $rating = new static();
        $rating->fill(array_merge($data, [
            'author_id' => $author->id,
            'author_type' => get_class($author),
        ]));

        $ratingable->ratings()->save($rating);

        return $rating;
    }

    public function createUniqueRating(Model $ratingable, array $data, Model $author): static
    {
        return static::updateOrCreate(
            [
                'author_id' => $author->id,
                'author_type' => get_class($author),
                'ratingable_id' => $ratingable->id,
                'ratingable_type' => get_class($ratingable),
            ],
            $data
        );
    }

    public function updateRating(int $id, array $data): static
    {
        $rating = static::findOrFail($id);
        $rating->update($data);

        return $rating;
    }

    public function deleteRating(int $id): bool
    {
        return static::findOrFail($id)->delete();
    }
}
