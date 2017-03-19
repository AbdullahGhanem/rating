<?php

namespace Ghanem\Rating\Models;

use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    /**
     * @var string
     */
    protected $table = 'ratings';

    /**
     * @var array
     */
    protected $fillable = ['rating', 'ratingable_id' , 'ratingable_type' , 'author_id', 'author_type'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function rateable()
    {
        return $this->morphTo('');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function author()
    {
        return $this->morphTo('author');
    }

    /**
     * @param Model $ratingable
     * @param $data
     * @param Model $author
     *
     * @return static
     */
}
