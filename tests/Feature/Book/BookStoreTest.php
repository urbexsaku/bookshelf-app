<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookStoreTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Genre $genre;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->genre = Genre::create([
            'name' => 'テストジャンル',
        ]);
    }

    /**
     * 書籍情報を登録できる
     */
    public function test_user_can_register_book(): void
    {
        $response = $this->actingAs($this->user)->post(route('books.store'), [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '2026-01-01',
            'description' => 'テスト書籍説明文',
            'image_url' => 'https://test.com/',
            'genres' => [$this->genre->id],
        ]);

        $book = Book::where('isbn', '1234567890123')->firstOrFail();

        $response->assertRedirect(route('books.show', $book));

        $this->assertDatabaseHas('books', [
            'title' => 'テスト書籍',
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
        $response = $this->actingAs($this->user)->post(route('books.store'), [
            'title' => '',
            'author' => 'テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '2026-01-01',
            'description' => 'テスト書籍説明文',
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
        $response = $this->actingAs($this->user)->post(route('books.store'), [
            'title' => 'テスト書籍',
            'author' => '',
            'isbn' => '1234567890123',
            'published_date' => '2026-01-01',
            'description' => 'テスト書籍説明文',
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
        $response = $this->actingAs($this->user)->post(route('books.store'), [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '',
            'published_date' => '2026-01-01',
            'description' => 'テスト書籍説明文',
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
            'isbn' => '1234567890123',
        ]);

        $response = $this->actingAs($this->user)->post(route('books.store'), [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '2026-01-01',
            'description' => 'テスト書籍説明文',
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
    public function test_guest_cannot_access_book_registration_page(): void
    {
        $response = $this->get(route('books.create'));

        $response->assertRedirect('/login');
    }

    /**
     * ゲストが書籍登録できない
     */
    public function test_guest_cannot_register_book(): void
    {
        $response = $this->post(route('books.store'), [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '2026-01-01',
            'description' => 'テスト書籍説明文',
            'image_url' => 'https://test.com/',
            'genres' => [$this->genre->id],
        ]);

        $response->assertRedirect('/login');

        $this->assertDatabaseMissing('books', [
            'isbn' => '1234567890123',
        ]);
    }
}
