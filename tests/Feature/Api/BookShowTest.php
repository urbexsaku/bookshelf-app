<?php

namespace Tests\Feature\Api;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookShowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 書籍詳細が取得できる
     */
    public function test_book_detail__can_be_retrieved(): void
    {
        $book = Book::factory()->create();

        $genre = Genre::create([
            'name' => 'テストジャンル',
        ]);

        $book->genres()->attach($genre->id);

        $review = Review::factory()->create([
            'book_id' => $book->id,
        ]);

        $response = $this->getJson("/api/v1/books/{$book->id}");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'author',
                'isbn',
                'genres',
                'average_rating',
                'review_count',
                'reviews' => [
                    '*' => [
                        'id',
                        'user_name',
                        'rating',
                        'comment'
                    ],
                ],
            ],
        ]);

        $response->assertJsonPath(
            'data.id',
            $book->id
        );

        $response->assertJsonPath(
            'data.genres.0.name',
            'テストジャンル'
        );

        $response->assertJsonPath(
            'data.reviews.0.user_name',
            $review->user->name
        );

        $response->assertJsonPath(
            'data.reviews.0.rating',
            $review->rating
        );

        $response->assertJsonPath(
            'data.reviews.0.comment',
            $review->comment
        );
    }

    /**
     * 存在しない書籍IDの場合、404エラーが返される
     */
    public function test_404_error_is_returned_when_book_id_does_not_exist(): void
    {
        $response = $this->getJson('/api/v1/books/9999');

        $response->assertStatus(404)
            ->assertJson([
                'error' => '書籍情報が見つかりませんでした',
            ]);
    }
}
