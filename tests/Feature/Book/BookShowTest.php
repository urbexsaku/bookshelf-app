<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookShowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 書籍詳細に必要な情報が表示される
     */
    public function test_book_detail_displays_book_information(): void
    {
        $book = Book::factory()->create([
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '2026-01-01',
            'description' => 'テスト書籍説明文',
            'image_url' => 'https://test.com/',
        ]);

        $genre = Genre::create([
            'name' => 'テストジャンル',
        ]);

        $book->genres()->attach($genre->id);

        $user = User::factory()->create();
        $reviewUser = User::factory()->create();
        $likeUsers = User::factory()->count(2)->create();

        $review = Review::create([
            'user_id' => $reviewUser->id,
            'book_id' => $book->id,
            'rating' => 1,
            'comment' => 'テストレビュー',
        ]);

        $review->likedByUsers()->attach($likeUsers->pluck('id'));

        $response = $this->actingAs($user)->get(route('books.show', $book));

        $response->assertStatus(200);
        $response->assertSee('テスト書籍');
        $response->assertSee('テスト著者');
        $response->assertSee('テストジャンル');
        $response->assertSee('1234567890123');
        $response->assertSee('2026-01-01');
        $response->assertSee('テスト書籍説明文');
        $response->assertSee('https://test.com/');
        $response->assertSee('テストレビュー');
        $response->assertSee('いいね (2)');
    }

    /**
     * ゲストがアクセスできる
     */
    public function test_guest_can_access_book_detail(): void
    {
        $book = Book::factory()->create();
        $response = $this->get(route('books.show', $book));

        $response->assertStatus(200);
    }
}
