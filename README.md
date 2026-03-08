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

Optionally publish the config file:

```bash
php artisan vendor:publish --tag=rating-config
```

## Usage

### Setup a Model

Add the `Ratingable` trait to any model you want to be ratable:

```php
use Ghanem\Rating\Traits\Ratingable;

class Post extends Model
{
    use Ratingable;
}
```

Add the `CanRate` trait to the author model:

```php
use Ghanem\Rating\Traits\CanRate;

class User extends Model
{
    use CanRate;
}
```

### Create a rating

```php
// From the ratable model
$rating = $post->rating(['rating' => 5], $user);

// From the author model
$rating = $user->rate($post, ['rating' => 5]);
```

### Create or update a unique rating

Only one rating per author per model:

```php
$rating = $post->ratingUnique(['rating' => 5], $user);

// Or from the author
$rating = $user->rateUnique($post, ['rating' => 5]);
```

### Update a rating

```php
$rating = $post->updateRating($ratingId, ['rating' => 3]);
```

### Delete a rating

```php
$post->deleteRating($ratingId);
```

### Rating with review body

```php
$post->rating([
    'rating' => 5,
    'body' => 'Great article!',
], $user);
```

### Scoped ratings (rate different aspects)

```php
$restaurant->rating(['rating' => 5, 'type' => 'food'], $user);
$restaurant->rating(['rating' => 3, 'type' => 'service'], $user);

$restaurant->avgRating('food');    // 5.0
$restaurant->avgRating('service'); // 3.0
$restaurant->avgRating();          // 4.0 (all types)
```

### Weighted ratings

```php
$post->rating(['rating' => 5, 'weight' => 2], $verifiedUser);
$post->rating(['rating' => 3, 'weight' => 1], $regularUser);

$post->weightedAvgRating(); // 4.33
```

### Aggregates

All aggregate methods accept an optional `$type` parameter for scoped ratings:

```php
$post->avgRating()          // average rating
$post->sumRating()          // sum of all ratings
$post->countRatings()       // total count
$post->countPositive()      // count where rating > 0
$post->countNegative()      // count where rating < 0
$post->ratingPercent()      // percentage (default max: 5)
$post->ratingPercent(10)    // percentage with custom max
$post->weightedAvgRating()  // weighted average
```

All available as attributes too:

```php
$post->avgRating
$post->sumRating
$post->countRatings
$post->countPositive
$post->countNegative
$post->ratingPercent
$post->weightedAvgRating
```

### Author queries (CanRate)

```php
$user->hasRated($post);          // bool
$user->getRating($post);         // Rating|null
$user->averageGivenRating();     // float
$user->totalGivenRatings();      // int
$user->ratings;                  // all ratings given
```

### Check if rated

```php
$post->isRatedBy($user);            // bool
$post->isRatedBy($user, 'food');    // bool (scoped)
```

### Query scopes

```php
// Eager load rating aggregates
Post::withAvgRating()->get();
Post::withSumRating()->get();
Post::withCountRatings()->get();

// Order by ratings
Post::orderByAvgRating()->get();        // desc by default
Post::orderByAvgRating('asc')->get();
Post::orderBySumRating()->get();
Post::orderByCountRatings()->get();

// Filter by minimum rating
Post::minAvgRating(3.5)->get();
Post::minSumRating(10)->get();

// Scoped by type
Post::withAvgRating('food')->get();
Post::orderByAvgRating('desc', 'food')->get();
```

## Validation

Configure rating bounds in `config/rating.php`:

```php
return [
    'min' => 1,
    'max' => 5,
    'allow_negative' => false,
];
```

Invalid ratings throw `Ghanem\Rating\Exceptions\InvalidRatingException`.

## Events

The package fires events on rating lifecycle:

- `Ghanem\Rating\Events\RatingCreated`
- `Ghanem\Rating\Events\RatingUpdated`
- `Ghanem\Rating\Events\RatingDeleted`

Each event has a public `$rating` property with the Rating model.

## Testing

```bash
composer test
```

## Sponsor

[Become a Sponsor](https://github.com/sponsors/AbdullahGhanem)
