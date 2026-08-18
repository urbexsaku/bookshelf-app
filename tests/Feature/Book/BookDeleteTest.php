<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected User $user1;

    protected User $user2;

    protected Book $book;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user1 = User::factory()->create();
        $this->user2 = User::factory()->create();

        $this->book = Book::factory()->create([
            'user_id' => $this->user1->id,
        ]);
    }

    /**
     * 書籍を削除できる
     */
    public function test_user_can_delete_book(): void
    {
        $genre = Genre::create([
            'name' => '削除テストジャンル',
        ]);

        $this->book->genres()->attach($genre->id);

        Review::factory()->create([
            'book_id' => $this->book->id,
        ]);

        $this->book->favoritedUsers()->attach($this->user1->id);

        $response = $this->actingAs($this->user1)->delete(route('books.destroy', $this->book));

        $response->assertRedirect(route('books.index'));

        // 書籍削除確認
        $this->assertDatabaseMissing('books', [
            'id' => $this->book->id,
        ]);

        // レビュー削除確認
        $this->assertDatabaseMissing('reviews', [
            'book_id' => $this->book->id,
        ]);

        // お気に入り削除確認
        $this->assertDatabaseMissing('favorites', [
            'book_id' => $this->book->id,
            'user_id' => $this->user1->id,
        ]);

        // ジャンル紐づけ解除確認
        $this->assertDatabaseMissing('book_genre', [
            'book_id' => $this->book->id,
            'genre_id' => $genre->id,
        ]);
    }

    /**
     * ゲストは書籍を削除できない
     */
    public function test_guest_cannot_delete_book(): void
    {
        $response = $this->delete(route('books.destroy', $this->book));

        $response->assertRedirect('/login');

        $this->assertDatabaseHas('books', [
            'id' => $this->book->id,
        ]);
    }

    /**
     * 他人の書籍は削除できない
     */
    public function test_book_registered_by_others_cannot_be_deleted(): void
    {
        $response = $this->actingAs($this->user2)->delete(route('books.destroy', $this->book));

        $response->assertForbidden();

        $this->assertDatabaseHas('books', [
            'id' => $this->book->id,
        ]);
    }
}
