<?php

namespace Ghanem\Rating\Tests;

use Ghanem\Rating\Events\RatingCreated;
use Ghanem\Rating\Events\RatingDeleted;
use Ghanem\Rating\Events\RatingUpdated;
use Ghanem\Rating\Exceptions\InvalidRatingException;
use Ghanem\Rating\Models\Rating;
use Ghanem\Rating\Tests\Models\Post;
use Ghanem\Rating\Tests\Models\User;
use Illuminate\Support\Facades\Event;

class RatingTest extends TestCase
{
    private Post $post;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->post = Post::create(['title' => 'Test Post']);
        $this->user = User::create(['name' => 'Test User']);
    }

    // -- Basic CRUD --

    public function test_can_create_rating(): void
    {
        $rating = $this->post->rating(['rating' => 5], $this->user);

        $this->assertInstanceOf(Rating::class, $rating);
        $this->assertEquals(5, $rating->rating);
        $this->assertEquals($this->user->id, $rating->author_id);
        $this->assertEquals(get_class($this->user), $rating->author_type);
        $this->assertCount(1, $this->post->ratings);
    }

    public function test_can_create_unique_rating(): void
    {
        $this->post->ratingUnique(['rating' => 5], $this->user);
        $this->post->ratingUnique(['rating' => 3], $this->user);

        $this->post->refresh();
        $this->assertCount(1, $this->post->ratings);
        $this->assertEquals(3, $this->post->ratings->first()->rating);
    }

    public function test_unique_rating_returns_rating_model(): void
    {
        $rating = $this->post->ratingUnique(['rating' => 5], $this->user);

        $this->assertInstanceOf(Rating::class, $rating);
        $this->assertEquals(5, $rating->rating);
    }

    public function test_can_update_rating(): void
    {
        $rating = $this->post->rating(['rating' => 5], $this->user);
        $updated = $this->post->updateRating($rating->id, ['rating' => 3]);

        $this->assertEquals(3, $updated->rating);
    }

    public function test_can_delete_rating(): void
    {
        $rating = $this->post->rating(['rating' => 5], $this->user);
        $result = $this->post->deleteRating($rating->id);

        $this->assertTrue($result);
        $this->post->refresh();
        $this->assertCount(0, $this->post->ratings);
    }

    // -- Aggregates --

    public function test_avg_rating(): void
    {
        $user2 = User::create(['name' => 'User 2']);

        $this->post->rating(['rating' => 5], $this->user);
        $this->post->rating(['rating' => 3], $user2);

        $this->assertEquals(4.0, $this->post->avgRating());
        $this->assertEquals(4.0, $this->post->avgRating);
    }

    public function test_sum_rating(): void
    {
        $user2 = User::create(['name' => 'User 2']);

        $this->post->rating(['rating' => 5], $this->user);
        $this->post->rating(['rating' => 3], $user2);

        $this->assertEquals(8.0, $this->post->sumRating());
        $this->assertEquals(8.0, $this->post->sumRating);
    }

    public function test_rating_percent(): void
    {
        $user2 = User::create(['name' => 'User 2']);

        $this->post->rating(['rating' => 5], $this->user);
        $this->post->rating(['rating' => 3], $user2);

        $this->assertEquals(80.0, $this->post->ratingPercent());
        $this->assertEquals(80.0, $this->post->ratingPercent);
    }

    public function test_rating_percent_with_custom_max(): void
    {
        $this->post->rating(['rating' => 8], $this->user);

        $this->assertEquals(80.0, $this->post->ratingPercent(10));
    }

    public function test_count_positive(): void
    {
        $user2 = User::create(['name' => 'User 2']);
        $user3 = User::create(['name' => 'User 3']);

        $this->post->rating(['rating' => 5], $this->user);
        $this->post->rating(['rating' => -1], $user2);
        $this->post->rating(['rating' => 3], $user3);

        $this->assertEquals(2, $this->post->countPositive());
        $this->assertEquals(2, $this->post->countPositive);
    }

    public function test_count_negative(): void
    {
        $user2 = User::create(['name' => 'User 2']);
        $user3 = User::create(['name' => 'User 3']);

        $this->post->rating(['rating' => 5], $this->user);
        $this->post->rating(['rating' => -1], $user2);
        $this->post->rating(['rating' => -2], $user3);

        $this->assertEquals(2, $this->post->countNegative());
        $this->assertEquals(2, $this->post->countNegative);
    }

    public function test_count_ratings(): void
    {
        $user2 = User::create(['name' => 'User 2']);

        $this->post->rating(['rating' => 5], $this->user);
        $this->post->rating(['rating' => 3], $user2);

        $this->assertEquals(2, $this->post->countRatings());
        $this->assertEquals(2, $this->post->countRatings);
    }

    public function test_no_ratings_returns_zero_values(): void
    {
        $this->assertEquals(0.0, $this->post->avgRating());
        $this->assertEquals(0.0, $this->post->sumRating());
        $this->assertEquals(0, $this->post->ratingPercent());
        $this->assertEquals(0, $this->post->countPositive());
        $this->assertEquals(0, $this->post->countNegative());
        $this->assertEquals(0, $this->post->countRatings());
    }

    public function test_rating_has_polymorphic_relations(): void
    {
        $rating = $this->post->rating(['rating' => 5], $this->user);

        $this->assertInstanceOf(Post::class, $rating->ratingable);
        $this->assertInstanceOf(User::class, $rating->author);
    }

    // -- Scoped Ratings (type) --

    public function test_scoped_rating_by_type(): void
    {
        $this->post->rating(['rating' => 5, 'type' => 'food'], $this->user);
        $this->post->rating(['rating' => 3, 'type' => 'service'], $this->user);

        $this->assertEquals(5.0, $this->post->avgRating('food'));
        $this->assertEquals(3.0, $this->post->avgRating('service'));
        $this->assertEquals(4.0, $this->post->avgRating());
    }

    public function test_scoped_sum_by_type(): void
    {
        $user2 = User::create(['name' => 'User 2']);

        $this->post->rating(['rating' => 5, 'type' => 'food'], $this->user);
        $this->post->rating(['rating' => 3, 'type' => 'food'], $user2);
        $this->post->rating(['rating' => 2, 'type' => 'service'], $this->user);

        $this->assertEquals(8.0, $this->post->sumRating('food'));
        $this->assertEquals(2.0, $this->post->sumRating('service'));
    }

    public function test_scoped_count_by_type(): void
    {
        $user2 = User::create(['name' => 'User 2']);

        $this->post->rating(['rating' => 5, 'type' => 'food'], $this->user);
        $this->post->rating(['rating' => 3, 'type' => 'food'], $user2);
        $this->post->rating(['rating' => 2, 'type' => 'service'], $this->user);

        $this->assertEquals(2, $this->post->countRatings('food'));
        $this->assertEquals(1, $this->post->countRatings('service'));
    }

    public function test_scoped_rating_percent_by_type(): void
    {
        $this->post->rating(['rating' => 4, 'type' => 'food'], $this->user);

        $this->assertEquals(80.0, $this->post->ratingPercent(5, 'food'));
    }

    // -- Review Body --

    public function test_rating_with_body(): void
    {
        $rating = $this->post->rating([
            'rating' => 5,
            'body' => 'Great article!',
        ], $this->user);

        $this->assertEquals('Great article!', $rating->body);
    }

    public function test_rating_body_nullable(): void
    {
        $rating = $this->post->rating(['rating' => 5], $this->user);

        $this->assertNull($rating->body);
    }

    // -- CanRate Trait (Author) --

    public function test_author_can_rate(): void
    {
        $rating = $this->user->rate($this->post, ['rating' => 5]);

        $this->assertInstanceOf(Rating::class, $rating);
        $this->assertEquals(5, $rating->rating);
    }

    public function test_author_can_rate_unique(): void
    {
        $this->user->rateUnique($this->post, ['rating' => 5]);
        $this->user->rateUnique($this->post, ['rating' => 3]);

        $this->assertEquals(1, $this->user->ratings()->count());
        $this->assertEquals(3, $this->user->ratings()->first()->rating);
    }

    public function test_author_has_rated(): void
    {
        $this->assertFalse($this->user->hasRated($this->post));

        $this->post->rating(['rating' => 5], $this->user);

        $this->assertTrue($this->user->hasRated($this->post));
    }

    public function test_author_get_rating(): void
    {
        $this->assertNull($this->user->getRating($this->post));

        $this->post->rating(['rating' => 5], $this->user);

        $rating = $this->user->getRating($this->post);
        $this->assertInstanceOf(Rating::class, $rating);
        $this->assertEquals(5, $rating->rating);
    }

    public function test_author_average_given_rating(): void
    {
        $post2 = Post::create(['title' => 'Post 2']);

        $this->user->rate($this->post, ['rating' => 5]);
        $this->user->rate($post2, ['rating' => 3]);

        $this->assertEquals(4.0, $this->user->averageGivenRating());
    }

    public function test_author_total_given_ratings(): void
    {
        $post2 = Post::create(['title' => 'Post 2']);

        $this->user->rate($this->post, ['rating' => 5]);
        $this->user->rate($post2, ['rating' => 3]);

        $this->assertEquals(2, $this->user->totalGivenRatings());
    }

    public function test_is_rated_by(): void
    {
        $this->assertFalse($this->post->isRatedBy($this->user));

        $this->post->rating(['rating' => 5], $this->user);

        $this->assertTrue($this->post->isRatedBy($this->user));
    }

    public function test_is_rated_by_with_type(): void
    {
        $this->post->rating(['rating' => 5, 'type' => 'food'], $this->user);

        $this->assertTrue($this->post->isRatedBy($this->user, 'food'));
        $this->assertFalse($this->post->isRatedBy($this->user, 'service'));
    }

    // -- Events --

    public function test_event_fired_on_create(): void
    {
        Event::fake([RatingCreated::class]);

        $this->post->rating(['rating' => 5], $this->user);

        Event::assertDispatched(RatingCreated::class);
    }

    public function test_event_fired_on_update(): void
    {
        $rating = $this->post->rating(['rating' => 5], $this->user);

        Event::fake([RatingUpdated::class]);

        $this->post->updateRating($rating->id, ['rating' => 3]);

        Event::assertDispatched(RatingUpdated::class);
    }

    public function test_event_fired_on_delete(): void
    {
        $rating = $this->post->rating(['rating' => 5], $this->user);

        Event::fake([RatingDeleted::class]);

        $this->post->deleteRating($rating->id);

        Event::assertDispatched(RatingDeleted::class);
    }

    public function test_event_fired_on_unique_create(): void
    {
        Event::fake([RatingCreated::class, RatingUpdated::class]);

        $this->post->ratingUnique(['rating' => 5], $this->user);

        Event::assertDispatched(RatingCreated::class);
        Event::assertNotDispatched(RatingUpdated::class);
    }

    public function test_event_fired_on_unique_update(): void
    {
        $this->post->ratingUnique(['rating' => 5], $this->user);

        Event::fake([RatingCreated::class, RatingUpdated::class]);

        $this->post->ratingUnique(['rating' => 3], $this->user);

        Event::assertNotDispatched(RatingCreated::class);
        Event::assertDispatched(RatingUpdated::class);
    }

    // -- Validation --

    public function test_validation_rejects_out_of_range_high(): void
    {
        config(['rating.min' => 1, 'rating.max' => 5]);

        $this->expectException(InvalidRatingException::class);

        $this->post->rating(['rating' => 6], $this->user);
    }

    public function test_validation_rejects_out_of_range_low(): void
    {
        config(['rating.min' => 1, 'rating.max' => 5]);

        $this->expectException(InvalidRatingException::class);

        $this->post->rating(['rating' => 0], $this->user);
    }

    public function test_validation_allows_within_range(): void
    {
        config(['rating.min' => 1, 'rating.max' => 5]);

        $rating = $this->post->rating(['rating' => 3], $this->user);

        $this->assertEquals(3, $rating->rating);
    }

    public function test_validation_rejects_negative_when_disabled(): void
    {
        config(['rating.allow_negative' => false]);

        $this->expectException(InvalidRatingException::class);

        $this->post->rating(['rating' => -1], $this->user);
    }

    public function test_validation_allows_negative_by_default(): void
    {
        $rating = $this->post->rating(['rating' => -1], $this->user);

        $this->assertEquals(-1, $rating->rating);
    }

    public function test_validation_on_update(): void
    {
        $rating = $this->post->rating(['rating' => 3], $this->user);

        config(['rating.min' => 1, 'rating.max' => 5]);

        $this->expectException(InvalidRatingException::class);

        $this->post->updateRating($rating->id, ['rating' => 10]);
    }

    // -- Weighted Ratings --

    public function test_weighted_avg_rating(): void
    {
        $user2 = User::create(['name' => 'User 2']);

        $this->post->rating(['rating' => 5, 'weight' => 2], $this->user);
        $this->post->rating(['rating' => 3, 'weight' => 1], $user2);

        // (5*2 + 3*1) / (2+1) = 13/3 ≈ 4.333
        $weighted = $this->post->weightedAvgRating();
        $this->assertEqualsWithDelta(4.333, $weighted, 0.01);
    }

    public function test_weighted_avg_falls_back_to_regular_avg(): void
    {
        $user2 = User::create(['name' => 'User 2']);

        $this->post->rating(['rating' => 5], $this->user);
        $this->post->rating(['rating' => 3], $user2);

        // No weights set — should fall back to regular avg
        $this->assertEquals(4.0, $this->post->weightedAvgRating());
    }

    public function test_weighted_avg_attribute(): void
    {
        $this->post->rating(['rating' => 5, 'weight' => 2], $this->user);

        $this->assertEquals(5.0, $this->post->weightedAvgRating);
    }

    // -- Query Scopes --

    public function test_scope_with_avg_rating(): void
    {
        $post2 = Post::create(['title' => 'Post 2']);

        $this->post->rating(['rating' => 5], $this->user);
        $post2->rating(['rating' => 3], $this->user);

        $posts = Post::withAvgRating()->get();

        $this->assertEquals(5.0, $posts->find($this->post->id)->ratings_avg_rating);
        $this->assertEquals(3.0, $posts->find($post2->id)->ratings_avg_rating);
    }

    public function test_scope_order_by_avg_rating(): void
    {
        $post2 = Post::create(['title' => 'Post 2']);

        $this->post->rating(['rating' => 3], $this->user);
        $post2->rating(['rating' => 5], $this->user);

        $posts = Post::orderByAvgRating()->get();

        $this->assertEquals($post2->id, $posts->first()->id);
        $this->assertEquals($this->post->id, $posts->last()->id);
    }

    public function test_scope_order_by_avg_rating_asc(): void
    {
        $post2 = Post::create(['title' => 'Post 2']);

        $this->post->rating(['rating' => 3], $this->user);
        $post2->rating(['rating' => 5], $this->user);

        $posts = Post::orderByAvgRating('asc')->get();

        $this->assertEquals($this->post->id, $posts->first()->id);
    }

    public function test_scope_with_count_ratings(): void
    {
        $user2 = User::create(['name' => 'User 2']);

        $this->post->rating(['rating' => 5], $this->user);
        $this->post->rating(['rating' => 3], $user2);

        $post = Post::withCountRatings()->find($this->post->id);

        $this->assertEquals(2, $post->ratings_count);
    }

    public function test_scope_order_by_count_ratings(): void
    {
        $post2 = Post::create(['title' => 'Post 2']);
        $user2 = User::create(['name' => 'User 2']);

        $this->post->rating(['rating' => 5], $this->user);
        $post2->rating(['rating' => 5], $this->user);
        $post2->rating(['rating' => 3], $user2);

        $posts = Post::orderByCountRatings()->get();

        $this->assertEquals($post2->id, $posts->first()->id);
    }

    public function test_scope_with_avg_rating_by_type(): void
    {
        $this->post->rating(['rating' => 5, 'type' => 'food'], $this->user);
        $this->post->rating(['rating' => 3, 'type' => 'service'], $this->user);

        $post = Post::withAvgRating('food')->find($this->post->id);

        $this->assertEquals(5.0, $post->ratings_avg_rating);
    }
}
