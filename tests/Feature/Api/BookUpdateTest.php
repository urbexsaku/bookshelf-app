<?php

namespace Tests\Feature\Api;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
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
        Sanctum::actingAs($this->user1, ['*']);

        $response = $this->putJson("/api/v1/books/{$this->book->id}", [
            'title' => '更新テスト書籍',
            'author' => '更新テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '2026-01-01',
            'description' => '更新テスト書籍説明文',
            'image_url' => 'https://test.com/',
            'genre_ids' => [$this->genre->id],
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('books', [
            'title' => '更新テスト書籍',
            'isbn' => '1234567890123',
        ]);

        $this->assertTrue(
            $this->book->genres->contains($this->genre)
        );
    }

    /**
     * 書籍タイトルが未入力の場合、バリデーションエラーが返される
     */
    public function test_validation_error_is_returned_when_title_is_empty(): void
    {
        Sanctum::actingAs($this->user1, ['*']);

        $response = $this->putJson("/api/v1/books/{$this->book->id}", [
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
        Sanctum::actingAs($this->user1, ['*']);

        $response = $this->putJson("/api/v1/books/{$this->book->id}", [
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
        Sanctum::actingAs($this->user1, ['*']);

        $response = $this->putJson("/api/v1/books/{$this->book->id}", [
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
            'isbn' => '1234567890124',
        ]);

        Sanctum::actingAs($this->user1, ['*']);

        $response = $this->putJson("/api/v1/books/{$this->book->id}", [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '1234567890124',
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
        Sanctum::actingAs($this->user1, ['*']);

        $response = $this->putJson("/api/v1/books/{$this->book->id}", [
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
    public function test_guest_cannot_update_book(): void
    {
        $response = $this->putJson("/api/v1/books/{$this->book->id}", [
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

    /**
     * 他人の書籍は更新できない
     */
    public function test_book_registered_by_others_cannot_be_updated(): void
    {
        Sanctum::actingAs($this->user2, ['*']);

        $response = $this->putJson("/api/v1/books/{$this->book->id}", [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '2026-01-01',
            'description' => 'テスト書籍説明文',
            'image_url' => 'https://test.com/',
            'genre_ids' => [$this->genre->id],
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'error' => 'この操作を実行する権限がありません',
            ]);
    }
}
