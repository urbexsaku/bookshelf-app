<?php

namespace Tests\Feature\Api;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
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
    public function test_user_can_create_book(): void
    {
        Sanctum::actingAs($this->user, ['*']);

        $response = $this->postJson('/api/v1/books', [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '2026-01-01',
            'description' => 'テスト書籍説明文',
            'image_url' => 'https://test.com/',
            'genre_ids' => [$this->genre->id],
        ]);

        $book = Book::where('isbn', '1234567890123')->firstOrFail();

        $response->assertStatus(201);

        $this->assertDatabaseHas('books', [
            'title' => 'テスト書籍',
            'isbn' => '1234567890123',
        ]);

        $this->assertTrue(
            $book->genres->contains($this->genre)
        );
    }

    /**
     * 書籍タイトルが未入力の場合、バリデーションエラーが返される
     */
    public function test_validation_error_is_returned_when_title_is_empty(): void
    {
        Sanctum::actingAs($this->user, ['*']);

        $response = $this->postJson('/api/v1/books', [
            'title' => '',
            'author' => 'テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '2026-01-01',
            'description' => 'テスト書籍説明文',
            'image_url' => 'https://test.com/',
            'genre_ids' => [$this->genre->id],
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'title',
        ]);

        $response->assertJsonPath(
            'errors.title.0',
            'タイトルを指定してください'
        );
    }

    /**
     * 著者名が未入力の場合、バリデーションエラーが返される
     */
    public function test_validation_error_is_returned_when_author_is_empty(): void
    {
        Sanctum::actingAs($this->user, ['*']);

        $response = $this->postJson('/api/v1/books', [
            'title' => 'テスト書籍',
            'author' => '',
            'isbn' => '1234567890123',
            'published_date' => '2026-01-01',
            'description' => 'テスト書籍説明文',
            'image_url' => 'https://test.com/',
            'genre_ids' => [$this->genre->id],
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'author',
        ]);

        $response->assertJsonPath(
            'errors.author.0',
            '著者名を指定してください'
        );
    }

    /**
     * ISBNが未入力の場合、バリデーションエラーが返される
     */
    public function test_validation_error_is_returned_when_isbn_is_empty(): void
    {
        Sanctum::actingAs($this->user, ['*']);

        $response = $this->postJson('/api/v1/books', [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '',
            'published_date' => '2026-01-01',
            'description' => 'テスト書籍説明文',
            'image_url' => 'https://test.com/',
            'genre_ids' => [$this->genre->id],
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'isbn',
        ]);

        $response->assertJsonPath(
            'errors.isbn.0',
            'ISBNを指定してください'
        );
    }

    /**
     * 重複するISBNの場合、バリデーションエラーが返される
     */
    public function test_validation_error_is_returned_when_isbn_is_already_registered(): void
    {
        Book::factory()->create([
            'isbn' => '1234567890123',
        ]);

        Sanctum::actingAs($this->user, ['*']);

        $response = $this->postJson('/api/v1/books', [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '2026-01-01',
            'description' => 'テスト書籍説明文',
            'image_url' => 'https://test.com/',
            'genre_ids' => [$this->genre->id],
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'isbn',
        ]);

        $response->assertJsonPath(
            'errors.isbn.0',
            'このISBNは既に登録されています'
        );
    }

    /**
     * 存在しないジャンルの場合、バリデーションエラーが返される
     */
    public function test_validation_error_is_returned_when_genre_id_does_not_exist(): void
    {
        Sanctum::actingAs($this->user, ['*']);

        $response = $this->postJson('/api/v1/books', [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '2026-01-01',
            'description' => 'テスト書籍説明文',
            'image_url' => 'https://test.com/',
            'genre_ids' => [9999],
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'genre_ids.0',
        ]);

        $response->assertJson([
            'errors' => [
                'genre_ids.0' => [
                    '指定されたジャンルが存在しません',
                ],
            ],
        ]);
    }

    /**
     * ゲストは書籍情報を登録できない
     */
    public function test_guest_cannot_create_book(): void
    {
        $response = $this->postJson('/api/v1/books', [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '2026-01-01',
            'description' => 'テスト書籍説明文',
            'image_url' => 'https://test.com/',
            'genre_ids' => [$this->genre->id],
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }
}
