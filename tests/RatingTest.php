<?php

namespace Ghanem\Rating\Tests;

use Ghanem\Rating\Models\Rating;
use Ghanem\Rating\Tests\Models\Post;
use Ghanem\Rating\Tests\Models\User;

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

        // (8 / (2 * 5) / 100) = 8 / 10 * 100 = 80
        $this->assertEquals(80.0, $this->post->ratingPercent());
        $this->assertEquals(80.0, $this->post->ratingPercent);
    }

    public function test_rating_percent_with_custom_max(): void
    {
        $this->post->rating(['rating' => 8], $this->user);

        // (8 / (1 * 10) / 100) = 8 / 10 * 100 = 80
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

    public function test_no_ratings_returns_zero_values(): void
    {
        $this->assertEquals(0.0, $this->post->avgRating());
        $this->assertEquals(0.0, $this->post->sumRating());
        $this->assertEquals(0, $this->post->ratingPercent());
        $this->assertEquals(0, $this->post->countPositive());
        $this->assertEquals(0, $this->post->countNegative());
    }

    public function test_rating_has_polymorphic_relations(): void
    {
        $rating = $this->post->rating(['rating' => 5], $this->user);

        $this->assertInstanceOf(Post::class, $rating->ratingable);
        $this->assertInstanceOf(User::class, $rating->author);
    }
}
