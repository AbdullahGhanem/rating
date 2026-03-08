<?php

namespace Ghanem\Rating\Tests\Models;

use Ghanem\Rating\Traits\Ratingable;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use Ratingable;

    protected $fillable = ['title'];
}
