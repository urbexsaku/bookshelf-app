<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected User $user1;

    protected User $user2;

    protected Book $book;

    protected Review $review;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user1 = User::factory()->create();
        $this->user2 = User::factory()->create();
        $this->book = Book::factory()->create();
        $this->review = Review::factory()->create([
            'user_id' => $this->user1->id,
            'book_id' => $this->book->id,
            'rating' => 1,
            'comment' => 'テストレビューコメント',
        ]);
    }

    /**
     * レビューを削除できる
     */
    public function test_user_can_delete_review(): void
    {
        $response = $this->actingAs($this->user1)->delete(route('reviews.destroy', $this->review));

        $response->assertRedirect(route('books.show', $this->book));

        $this->assertDatabaseMissing('reviews', [
            'id' => $this->review->id,
        ]);
    }

    /**
     * ゲストはレビューを削除できない
     */
    public function test_guest_cannot_delete_review(): void
    {
        $response = $this->delete(route('reviews.destroy', $this->review));

        $response->assertRedirect('/login');

        $this->assertDatabaseHas('reviews', [
            'id' => $this->review->id,
        ]);
    }

    /**
     * 他人のレビューは削除できない
     */
    public function test_review_posted_by_others_cannot_be_deleted(): void
    {
        $response = $this->actingAs($this->user2)->delete(route('reviews.destroy', $this->review));

        $response->assertForbidden();

        $this->assertDatabaseHas('reviews', [
            'id' => $this->review->id,
        ]);
    }
}
