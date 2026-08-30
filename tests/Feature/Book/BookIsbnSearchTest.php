<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BookIsbnSearchTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    /**
     * ISBNで検索して書籍情報を取得できる。
     */
    public function test_book_information_can_be_retrieved_by_isbn(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([
                'items' => [
                    [
                        'volumeInfo' => [
                            'title' => 'テスト書籍',
                            'authors' => ['テスト著者'],
                            'publishedDate' => '2020-01-01',
                            'description' => 'テスト説明',
                            'imageLinks' => [
                                'thumbnail' => 'https://example.com/image.jpg',
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->getJson(route('books.isbn.search', [
            'isbn' => '1234567890123',
        ]));

        $response->assertStatus(200);

        $response->assertJson([
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'published_date' => '2020-01-01',
            'description' => 'テスト説明',
            'image_url' => 'https://example.com/image.jpg',
        ]);
    }

    /**
     * 存在しないISBNの場合、404エラーが返される。
     */
    public function test_error_message_is_displayed_when_isbn_does_not_exist(): void
    {
        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([
                'totalItems' => 0,
                'items' => [],
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->getJson(route('books.isbn.search', [
            'isbn' => '1234567890123',
        ]));

        $response->assertStatus(404)
            ->assertJson([
                'error' => '該当する書籍が見つかりません。',
            ]);
    }

    /**
     * Google Books APIとの接続エラーの場合、422エラーが返される。
     */
    public function test_google_books_api_connection_error_returns_422(): void
    {
        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response(
                ['error' => ['message' => 'API Error']],
                500
            ),
        ]);

        $response = $this->actingAs($this->user)->getJson(route('books.isbn.search', [
            'isbn' => '1234567890123',
        ]));

        $response->assertStatus(422)
            ->assertJson([
                'error' => 'Google Books APIへの接続に失敗しました。',
            ]);
    }
}
