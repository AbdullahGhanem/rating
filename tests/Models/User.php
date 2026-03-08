<?php

namespace Ghanem\Rating\Tests\Models;

use Ghanem\Rating\Traits\CanRate;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use CanRate;

    protected $fillable = ['name'];
}
