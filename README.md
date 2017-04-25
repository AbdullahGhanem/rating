[![Latest Stable Version](https://poser.pugx.org/ghanem/rating/v/stable.svg)](https://packagist.org/packages/ghanem/rating) [![License](https://poser.pugx.org/ghanem/rating/license.svg)](https://packagist.org/packages/ghanem/rating)

[![Total Downloads](https://poser.pugx.org/ghanem/rating/downloads.svg)](https://packagist.org/packages/ghanem/rating)

# Laravel Rating
![https://fbcdn-sphotos-e-a.akamaihd.net/hphotos-ak-xaf1/t31.0-8/11056081_971241142949083_1060220626805694678_o.png](https://fbcdn-sphotos-e-a.akamaihd.net/hphotos-ak-xaf1/t31.0-8/11056081_971241142949083_1060220626805694678_o.png)
Rating system for laravel 5

## Installation

First, pull in the package through Composer.

```js
composer require ghanem/rating
```
or add this in your project's composer.json file .
````
"require": {
  "Ghanem/Rating": "1.*",
}
````

And then include the service provider within `app/config/app.php`.

```php
'providers' => [
    Ghanem\Rating\RatingServiceProvider::class
];
```

-----
## Getting started
After the package is correctly installed, you need to generate the migration.
````
php artisan rating:migration
````

It will generate the `<timestamp>_create_ratings_table.php` migration. You may now run it with the artisan migrate command:
````
php artisan migrate
````

After the migration, one new table will be present, `ratings`.

## Usage
### Setup a Model
```php
<?php

namespace App;

use Ghanem\Rating\Traits\Ratingable as Rating;
use Illuminate\Database\Eloquent\Model;

class Post extends Model implements Rating
{
    use Rating;
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

### Create or update a unique rating
```php
$user = User::first();
$post = Post::first();

$rating = $post->ratingUnique([
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

### fetch the Sum rating:
````php
$post->sumRating

// $post->sumRating() also works for this.
```` 

### fetch the average rating:
````php
$post->avgRating

// $post->avgRating() also works for this.
````

### fetch the rating percentage. 
This is also how you enforce a maximum rating value.
````php
$post->ratingPercent

$post->ratingPercent(10)); // Ten star rating system
// Note: The value passed in is treated as the maximum allowed value.
// This defaults to 5 so it can be called without passing a value as well.
````

### Count positive rating:
````php
$post->countPositive

// $post->countPositive() also works for this.
````

### Count negative rating:
````php
$post->countNegative

// $post->countNegative() also works for this.
````
