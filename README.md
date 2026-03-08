[![Latest Stable Version](https://poser.pugx.org/ghanem/rating/v/stable.svg)](https://packagist.org/packages/ghanem/rating) [![License](https://poser.pugx.org/ghanem/rating/license.svg)](https://packagist.org/packages/ghanem/rating) [![Total Downloads](https://poser.pugx.org/ghanem/rating/downloads.svg)](https://packagist.org/packages/ghanem/rating)

# Laravel Rating

Rating system for Laravel 8, 9, 10, 11 & 12.

## Installation

```bash
composer require ghanem/rating
```

The package uses Laravel's auto-discovery, so no need to manually register the service provider.

## Getting started

Publish and run the migration:

```bash
php artisan vendor:publish --provider="Ghanem\Rating\RatingServiceProvider"
php artisan migrate
```

This creates the `ratings` table.

## Usage

### Setup a Model

Add the `Ratingable` trait to any model you want to be ratable:

```php
<?php

namespace App\Models;

use Ghanem\Rating\Traits\Ratingable;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use Ratingable;
}
```

### Create a rating

```php
$user = User::first();
$post = Post::first();

$rating = $post->rating([
    'rating' => 5
], $user);
```

### Create or update a unique rating

Only one rating per author per model:

```php
$rating = $post->ratingUnique([
    'rating' => 5
], $user);
```

### Update a rating

```php
$rating = $post->updateRating($ratingId, [
    'rating' => 3
]);
```

### Delete a rating

```php
$post->deleteRating($ratingId);
```

### Average rating

```php
$post->avgRating    // attribute
$post->avgRating()  // method
```

### Sum rating

```php
$post->sumRating    // attribute
$post->sumRating()  // method
```

### Rating percentage

```php
$post->ratingPercent       // defaults to max of 5
$post->ratingPercent(10)   // ten star rating system
```

### Count positive ratings

```php
$post->countPositive    // attribute
$post->countPositive()  // method
```

### Count negative ratings

```php
$post->countNegative    // attribute
$post->countNegative()  // method
```

## Testing

```bash
composer test
```

## Sponsor

[Become a Sponsor](https://github.com/sponsors/AbdullahGhanem)
