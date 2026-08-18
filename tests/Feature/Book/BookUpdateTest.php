<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected User $user1;

    protected User $user2;

    protected Book $book;

    protected Genre $genre;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user1 = User::factory()->create();
        $this->user2 = User::factory()->create();

        $this->book = Book::factory()->create([
            'user_id' => $this->user1->id,
            'isbn' => '1234567890123',
        ]);

        $this->genre = Genre::create([
            'name' => '更新テストジャンル',
        ]);
    }

    /**
     * 書籍情報を更新できる
     */
    public function test_user_can_update_book(): void
    {
        $response = $this->actingAs($this->user1)->put(route('books.update', $this->book), [
            'title' => '更新テスト書籍',
            'author' => '更新テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '2026-01-01',
            'description' => '更新テスト書籍説明文',
            'image_url' => 'https://test.com/',
            'genres' => [$this->genre->id],
        ]);

        $book = Book::where('isbn', '1234567890123')->firstOrFail();

        $response->assertRedirect(route('books.show', $book));

        $this->assertDatabaseHas('books', [
            'title' => '更新テスト書籍',
            'isbn' => '1234567890123',
        ]);

        $this->assertTrue(
            $book->genres->contains($this->genre)
        );
    }

    /**
     * 書籍タイトルが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_validation_message_is_displayed_when_title_is_empty(): void
    {
        $response = $this->actingAs($this->user1)->put(route('books.update', $this->book), [
            'title' => '',
            'author' => '更新テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '2026-01-01',
            'description' => '更新テスト書籍説明文',
            'image_url' => 'https://test.com/',
            'genres' => [$this->genre->id],
        ]);

        $response->assertSessionHasErrors('title');

        $this->assertEquals(
            'タイトルを入力してください',
            session('errors')->first('title')
        );
    }

    /**
     * 著者名が未入力の場合、バリデーションメッセージが表示される
     */
    public function test_validation_message_is_displayed_when_author_is_empty(): void
    {
        $response = $this->actingAs($this->user1)->put(route('books.update', $this->book), [
            'title' => '更新テスト書籍',
            'author' => '',
            'isbn' => '1234567890123',
            'published_date' => '2026-01-01',
            'description' => '更新テスト書籍説明文',
            'image_url' => 'https://test.com/',
            'genres' => [$this->genre->id],
        ]);

        $response->assertSessionHasErrors('author');

        $this->assertEquals(
            '著者名を入力してください',
            session('errors')->first('author')
        );
    }

    /**
     * ISBNが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_validation_message_is_displayed_when_isbn_is_empty(): void
    {
        $response = $this->actingAs($this->user1)->put(route('books.update', $this->book), [
            'title' => '更新テスト書籍',
            'author' => '更新テスト著者',
            'isbn' => '',
            'published_date' => '2026-01-01',
            'description' => '更新テスト書籍説明文',
            'image_url' => 'https://test.com/',
            'genres' => [$this->genre->id],
        ]);

        $response->assertSessionHasErrors('isbn');

        $this->assertEquals(
            'ISBNを入力してください',
            session('errors')->first('isbn')
        );
    }

    /**
     * 重複するISBNの場合、バリデーションメッセージが表示される
     */
    public function test_validation_message_is_displayed_when_isbn_is_already_registered(): void
    {
        Book::factory()->create([
            'isbn' => '1234567890124',
        ]);

        $response = $this->actingAs($this->user1)->put(route('books.update', $this->book), [
            'title' => '更新テスト書籍',
            'author' => '更新テスト著者',
            'isbn' => '1234567890124',
            'published_date' => '2026-01-01',
            'description' => '更新テスト書籍説明文',
            'image_url' => 'https://test.com/',
            'genres' => [$this->genre->id],
        ]);

        $response->assertSessionHasErrors('isbn');

        $this->assertEquals(
            'このISBNは既に登録されています',
            session('errors')->first('isbn')
        );
    }

    /**
     * ゲストがアクセスできない
     */
    public function test_guest_cannot_access_book_edit_page(): void
    {
        $response = $this->get(route('books.edit', $this->book));

        $response->assertRedirect('/login');
    }

    /**
     * ゲストが書籍更新できない
     */
    public function test_guest_cannot_update_book(): void
    {
        $response = $this->put(route('books.update', $this->book), [
            'title' => '更新テスト書籍',
            'author' => '更新テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '2026-01-01',
            'description' => '更新テスト書籍説明文',
            'image_url' => 'https://test.com/',
            'genres' => [$this->genre->id],
        ]);

        $response->assertRedirect('/login');

        $this->assertDatabaseMissing('books', [
            'title' => '更新テスト書籍',
        ]);
    }

    /**
     * 他人の書籍は更新できない
     */
    public function test_book_registered_by_others_cannot_be_updated(): void
    {
        $response = $this->actingAs($this->user2)->put(route('books.update', $this->book), [
            'title' => '更新テスト書籍',
            'author' => '更新テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '2026-01-01',
            'description' => '更新テスト書籍説明文',
            'image_url' => 'https://test.com/',
            'genres' => [$this->genre->id],
        ]);

        $response->assertForbidden();

        $this->assertDatabaseMissing('books', [
            'title' => '更新テスト書籍',
        ]);
    }
}
