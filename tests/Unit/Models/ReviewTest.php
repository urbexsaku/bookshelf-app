<?php

namespace Tests\Unit\Models;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Reviewに紐づくBookを取得できる
     */
    public function test_review_can_get_related_book(): void
    {
        $book = Book::factory()->create();

        $review = Review::factory()->create([
            'book_id' => $book->id,
        ]);

        $this->assertTrue($review->book->is($book));
    }

    /**
     * ReviewにいいねしたUserを取得できる
     */
    public function test_review_can_get_liked_by_users(): void
    {
        $user = User::factory()->create();

        $review = Review::factory()->create();

        $review->likedByUsers()->attach($user->id);

        $this->assertTrue($review->likedByUsers->contains($user));
    }
}
