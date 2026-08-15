<?php

namespace Tests\Unit\Models;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Userに紐づくお気に入り書籍を取得できる
     */
    public function test_user_can_get_favorite_books(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $user->favoriteBooks()->attach($book->id);

        $this->assertTrue($user->favoriteBooks->contains($book));
    }

    /**
     * Userに紐づくいいねしたReviewを取得できる
     */
    public function test_user_can_get_liked_reviews(): void
    {
        $user = User::factory()->create();

        $review = Review::factory()->create();

        $user->likedReviews()->attach($review->id);

        $this->assertTrue($user->likedReviews->contains($review));
    }
}
