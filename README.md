[![Latest Stable Version](https://poser.pugx.org/ghanem/rating/v/stable.svg)](https://packagist.org/packages/ghanem/rating) [![License](https://poser.pugx.org/ghanem/rating/license.svg)](https://packagist.org/packages/ghanem/rating)

[![Total Downloads](https://poser.pugx.org/ghanem/rating/downloads.svg)](https://packagist.org/packages/ghanem/rating)

# Laravel Rating
Rating system for laravel 5

## Installation

First, pull in the package through Composer.

```js
composer require ghanem/rating
```

And then include the service provider within `app/config/app.php`.

```php
'providers' => [
    Ghanem\Rating\RatingServiceProvider::class
];
```

At last you need to publish and run the migration.
```
php artisan vendor:publish --provider="Ghanem\Rating\RatingServiceProvider" && php artisan migrate
```

-----

### Setup a Model
```php
<?php

namespace App;

use Ghanem\Rating\Contracts\Rating;
use Ghanem\Rating\Traits\Ratingable as RatingTrait;
use Illuminate\Database\Eloquent\Model;

class Post extends Model implements Rating
{
    use RatingTrait;
}
```

### Create a rating
```php
$user = User::first();
$post = Post::first();

$rating = $post->rating([
    'rating' => 5
], $user);

dd($rating);
```

### Update a rating
```php
$rating = $post->updateRating(1, [
    'rating' => 3
]);
```

### Delete a rating:
```php
$post->deleteRating(1);
```

### fetch the average rating:
````php
$post->averageRating()
````

### fetch the rating percentage. 
This is also how you enforce a maximum rating value.
````php
$post->ratingPercent()

$post->ratingPercent(10)); // Ten star rating system
// Note: The value passed in is treated as the maximum allowed value.
// This defaults to 5 so it can be called without passing a value as well.
````
