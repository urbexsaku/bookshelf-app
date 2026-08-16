<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Book $book;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->book = Book::factory()->create([
            'title' => 'テスト書籍',
        ]);
    }

    /**
     * お気に入り登録した書籍だけがお気に入り画面に表示される
     */
    public function test_favorite_page_displays_only_favorited_books(): void
    {
        $this->book->favoritedUsers()->attach($this->user->id);

        Book::factory()->create([
            'title' => 'お気に入りではない書籍',
        ]);

        $response = $this->actingAs($this->user)->get(route('favorites.index'));

        $response->assertStatus(200);
        $response->assertSee('テスト書籍');
        $response->assertDontSee('お気に入りではない書籍');
    }

    /**
     * お気に入りを登録できる
     */
    public function test_user_can_favorite_book(): void
    {
        $response = $this->actingAs($this->user)
            ->from(route('books.show', $this->book))
            ->post(route('favorites.toggle', $this->book));

        $response->assertRedirect(route('books.show', $this->book));

        $this->assertDatabaseHas('favorites', [
            'book_id' => $this->book->id,
            'user_id' => $this->user->id,
        ]);
    }

    /**
     * お気に入り登録を解除できる
     */
    public function test_user_can_unfavorite_book(): void
    {
        $this->book->favoritedUsers()->attach($this->user->id);

        $response = $this->actingAs($this->user)
            ->from(route('books.show', $this->book))
            ->post(route('favorites.toggle', $this->book));

        $response->assertRedirect(route('books.show', $this->book));

        $this->assertDatabaseMissing('favorites', [
            'book_id' => $this->book->id,
            'user_id' => $this->user->id,
        ]);
    }

    /**
     * ゲストはお気に入り一覧画面にアクセスできない
     */
    public function test_guest_cannot_access_favorite_page(): void
    {
        $response = $this->get(route('favorites.index'));

        $response->assertRedirect('/login');
    }

    /**
     * ゲストはお気に入り登録できない
     */
    public function test_guest_cannot_favorite_book(): void
    {
        $response = $this->from(route('books.show', $this->book))
            ->post(route('favorites.toggle', $this->book));

        $response->assertRedirect('/login');

        $this->assertDatabaseMissing('favorites', [
            'book_id' => $this->book->id,
            'user_id' => $this->user->id,
        ]);
    }
}
