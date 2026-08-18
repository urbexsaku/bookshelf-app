<?php

namespace Tests\Unit\Models;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Bookに紐づくUserを取得できる
     */
    public function test_book_can_get_related_user(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertTrue($book->user->is($user));
    }

    /**
     * Bookに紐づくReviewを取得できる
     */
    public function test_book_can_get_related_reviews(): void
    {
        $book = Book::factory()->create();

        $review = Review::factory()->create([
            'book_id' => $book->id,
        ]);

        $this->assertTrue($book->reviews->contains($review));
    }

    /**
     * Bookに紐づくGenreを取得できる
     */
    public function test_book_can_get_related_genres(): void
    {
        $book = Book::factory()->create();

        $genre = Genre::create([
            'name' => 'テストジャンル',
        ]);

        $book->genres()->attach($genre->id);

        $this->assertTrue($book->genres->contains($genre));
    }

    /**
     * Bookをお気に入り登録しているUserを取得できる
     */
    public function test_book_can_get_favorited_users(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $book->favoritedUsers()->attach($user->id);

        $this->assertTrue($book->favoritedUsers->contains($user));
    }
}
