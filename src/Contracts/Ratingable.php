<?php

namespace Ghanem\Rating\Contracts;

use Illuminate\Database\Eloquent\Model;

interface Ratingable
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function ratings();

    /**
     *
     * @return mixed
     */
    public function averageRating();
    
    /**
     *
     * @return mixed
     */
    public function countRatings();

    /**
     *
     * @return mixed
     */
    public function sumRating();

    /**
     * @param $maxed
     *
     * @return mixed
     */ 
    public function ratingPercent($max = 5);
    
    /**
     * @param $data
     * @param Model      $author
     * @param Model|null $parent
     *
     * @return mixed
     */
    public function rating($data, Model $author, Model $parent = null);

    /**
     * @param $id
     * @param $data
     * @param Model|null $parent
     *
     * @return mixed
     */
    public function updateRating($id, $data, Model $parent = null);

    /**
     * @param $id
     *
     * @return mixed
     */
    public function deleteRating($id);
}
