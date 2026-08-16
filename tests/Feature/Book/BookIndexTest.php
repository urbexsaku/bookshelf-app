<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookIndexTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    /**
     * 書籍一覧に書籍タイトル・著者・ジャンルが表示される
     */
    public function test_book_list_displays_book_information(): void
    {
        $book = Book::factory()->create([
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
        ]);

        $genre = Genre::create([
            'name' => 'テストジャンル',
        ]);

        $book->genres()->attach($genre->id);

        $response = $this->actingAs($this->user)->get(route('books.index'));

        $response->assertStatus(200);
        $response->assertSee('テスト書籍');
        $response->assertSee('テスト著者');
        $response->assertSee('テストジャンル');
    }

    /**
     * 11件以上の場合、1ページに10件の書籍が表示される
     */
    public function test_book_list_displays_10_books_per_page(): void
    {
        Book::factory()->count(11)->create();

        $response = $this->actingAs($this->user)->get(route('books.index'));

        $response->assertStatus(200);

        $books = $response->viewData('books');

        $this->assertCount(10, $books);
    }

    /**
     * 書籍に紐づく複数のジャンルが表示される
     */
    public function test_book_list_displays_multiple_genres(): void
    {
        $book = Book::factory()->create();

        $genre1 = Genre::create([
            'name' => '小説',
        ]);

        $genre2 = Genre::create([
            'name' => 'ミステリー',
        ]);

        $book->genres()->attach([$genre1->id, $genre2->id]);

        $response = $this->actingAs($this->user)->get(route('books.index'));

        $response->assertStatus(200);
        $response->assertSee('小説');
        $response->assertSee('ミステリー');
    }

    /**
     * ゲストがアクセスできる
     */
    public function test_guest_can_access_book_list(): void
    {
        $response = $this->get(route('books.index'));

        $response->assertStatus(200);
    }
}
