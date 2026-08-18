<?php

namespace Tests\Feature\Api;

use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookIndexTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 書籍一覧が取得できる
     */
    public function test_book_list_can_be_retrieved(): void
    {
        Book::factory()->count(100)->create();

        $response = $this->getJson('/api/v1/books');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'author',
                    'isbn',
                    'genres',
                ],
            ],
            'meta' => [
                'current_page',
                'last_page',
                'per_page',
                'total',
            ],
        ]);

        $response->assertJsonPath('meta.current_page', 1);
        $response->assertJsonPath('meta.per_page', 20);
        $response->assertJsonPath('meta.total', 100);
    }

    /**
     * キーワードで指定した書籍一覧が取得できる
     */
    public function test_book_can_be_filtered_by_keyword(): void
    {
        Book::factory()->create([
            'title' => 'テスト用書籍１',
            'author' => '山田太郎',
        ]);

        Book::factory()->create([
            'title' => 'テスト用書籍２',
            'author' => '田中花子',
        ]);

        Book::factory()->create([
            'title' => '他の書籍',
            'author' => '佐藤一郎',
        ]);

        $response = $this->getJson('/api/v1/books?keyword=テスト');

        $response
            ->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment([
                'title' => 'テスト用書籍１',
            ])
            ->assertJsonFragment([
                'title' => 'テスト用書籍２',
            ])
            ->assertJsonMissing([
                'title' => '他の書籍',
            ]);
    }

    /**
     * 存在しないジャンルの場合、バリデーションエラーが返される
     */
    public function test_validation_error_is_returned_when_genre_id_does_not_exist(): void
    {
        $response = $this->getJson('/api/v1/books?genre_id=999');

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'genre_id',
        ]);

        $response->assertJsonPath(
            'errors.genre_id.0',
            '指定されたジャンルが存在しません'
        );
    }

    /**
     * genre_id, page, per_pageが整数でない場合、バリデーションエラーが返される
     */
    public function test_validation_error_is_returned_when_query_parameters_are_not_integers(): void
    {
        $response = $this->getJson(
            '/api/v1/books?genre_id=1.5&page=1.5&per_page=1.5'
        );

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'genre_id',
            'page',
            'per_page',
        ]);

        $response->assertJsonPath(
            'errors.genre_id.0',
            'ジャンルIDは整数で指定してください'
        );

        $response->assertJsonPath(
            'errors.page.0',
            'pageは整数で指定してください'
        );

        $response->assertJsonPath(
            'errors.per_page.0',
            'per_pageは整数で指定してください'
        );
    }

    /**
     * page・per_pageが0の場合、バリデーションエラーが返される
     */
    public function test_validation_error_is_returned_when_query_parameters_are_zeros(): void
    {
        $response = $this->getJson(
            '/api/v1/books?page=0&per_page=0'
        );

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'page',
            'per_page',
        ]);

        $response->assertJsonPath(
            'errors.page.0',
            'pageは1以上で指定してください'
        );

        $response->assertJsonPath(
            'errors.per_page.0',
            'per_pageは1以上100以下で指定してください'
        );
    }

    /**
     * per_pageが100を超える場合、バリデーションエラーが返される
     */
    public function test_validation_error_is_returned_when_per_page_is_over_100(): void
    {
        $response = $this->getJson(
            '/api/v1/books?per_page=101'
        );

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'per_page',
        ]);

        $response->assertJsonPath(
            'errors.per_page.0',
            'per_pageは1以上100以下で指定してください'
        );
    }
}
