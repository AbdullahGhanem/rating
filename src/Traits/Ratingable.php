<?php

namespace Ghanem\Rating\Traits;

use PackageBackup\Ratingable\Models\Rating;
use Illuminate\Database\Eloquent\Model;

trait Ratingable
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function ratings()
    {
        return $this->morphMany(Rating::class, 'ratingable');
    }

    /**
     *
     * @return mix
     */
    public function averageRating()
    {
        return $this->ratings()
            ->selectRaw('AVG(rating) as averageRating')
            ->pluck('averageRating');
    }

    /**
     *
     * @return mix
     */
    public function sumRating()
    {
        return $this->ratings()
            ->selectRaw('SUM(rating) as sumRating')
            ->pluck('sumRating');
    }

    /**
     * @param $max
     *
     * @return mix
     */ 
    public function ratingPercent($max = 5)
    {
        $ratings = $this->ratings();
        $quantity = $ratings->count();
        $total = $ratings->selectRaw('SUM(rating) as total')->pluck('total');
        return ($quantity * $max) > 0 ? $total / (($quantity * $max) / 100) : 0;
    }
    
    /**
     * @param $data
     * @param Model      $author
     * @param Model|null $parent
     *
     * @return static
     */
    public function rating($data, Model $author, Model $parent = null)
    {
        return (new Rating())->createRating($this, $data, $author);
    }

    /**
     * @param $id
     * @param $data
     * @param Model|null $parent
     *
     * @return mixed
     */
    public function updateRating($id, $data, Model $parent = null)
    {
        return (new Rating())->updateRating($id, $data);
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function deleteRating($id)
    {
        return (new Rating())->deleteRating($id);
    }
}
