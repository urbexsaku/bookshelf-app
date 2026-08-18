<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LikeTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Book $book;

    protected Review $review;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->book = Book::factory()->create();
        $this->review = Review::factory()->create([
            'book_id' => $this->book->id,
        ]);
    }

    /**
     * レビューをいいねできる
     */
    public function test_user_can_like_review(): void
    {
        $response = $this->actingAs($this->user)
            ->from(route('books.show', $this->book))
            ->post(route('reviews.like', $this->review));

        $response->assertRedirect(route('books.show', $this->book));

        $this->assertDatabaseHas('review_likes', [
            'review_id' => $this->review->id,
            'user_id' => $this->user->id,
        ]);
    }

    /**
     * レビューのいいねを解除できる
     */
    public function test_user_can_unlike_review(): void
    {
        $this->review->likedByUsers()->attach($this->user->id);

        $response = $this->actingAs($this->user)
            ->from(route('books.show', $this->book))
            ->post(route('reviews.like', $this->review));

        $response->assertRedirect(route('books.show', $this->book));

        $this->assertDatabaseMissing('review_likes', [
            'review_id' => $this->review->id,
            'user_id' => $this->user->id,
        ]);
    }

    /**
     * ゲストはレビューをいいねできない
     */
    public function test_guest_cannot_like_review(): void
    {
        $response = $this->from(route('books.show', $this->book))
            ->post(route('reviews.like', $this->review));

        $response->assertRedirect('/login');

        $this->assertDatabaseMissing('review_likes', [
            'review_id' => $this->review->id,
            'user_id' => $this->user->id,
        ]);
    }
}
