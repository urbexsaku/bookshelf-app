<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
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
     * 書籍一覧に書籍タイトル・著者・ジャンルが表示される。
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
     * 11件以上の場合、1ページに10件の書籍が表示される。
     */
    public function test_book_list_displays_10_books_per_page(): void
    {
        Book::factory()->count(11)->create();

        $response = $this->get(route('books.index'));

        $response->assertStatus(200);

        $books = $response->viewData('books');

        $this->assertCount(10, $books);
    }

    /**
     * 書籍に紐づく複数のジャンルが表示される。
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

        $response = $this->get(route('books.index'));

        $response->assertStatus(200);
        $response->assertSee('小説');
        $response->assertSee('ミステリー');
    }

    /**
     * キーワードで指定した書籍一覧が取得できる。
     */
    public function test_book_can_be_filtered_by_keyword(): void
    {
        Book::factory()->create([
            'title' => 'テスト用書籍名',
        ]);

        Book::factory()->create([
            'author' => 'テスト用書籍著者',
        ]);

        Book::factory()->create([
            'title' => '他の書籍',
        ]);

        $response = $this->get(route('books.index', [
            'keyword' => 'テスト',
        ]));

        $response->assertStatus(200);
        $response->assertSee('テスト用書籍名');
        $response->assertSee('テスト用書籍著者');
        $response->assertDontSee('他の書籍');
    }

    /**
     * ジャンルで指定した書籍一覧が取得できる。
     */
    public function test_book_can_be_filtered_by_genre(): void
    {
        $genre = Genre::create([
            'name' => 'テストジャンル',
        ]);

        $otherGenre = Genre::create([
            'name' => '別ジャンル',
        ]);

        $book = Book::factory()->create([
            'title' => 'テスト書籍',
        ]);

        $otherGenreBook = Book::factory()->create([
            'title' => '別ジャンルの書籍',
        ]);

        $genre->books()->attach($book->id);
        $otherGenre->books()->attach($otherGenreBook->id);

        $response = $this->get(route('books.index', [
            'genre' => $genre->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('テスト書籍');
        $response->assertDontSee('別ジャンルの書籍');
    }

    /**
     * 指定した登録順（新しい・古い）に書籍一覧が表示される。
     */
    public function test_book_can_be_sorted_by_creation_date(): void
    {
        Book::factory()->create([
            'title' => '2日前に登録',
            'created_at' => now()->subDays(2),
        ]);

        Book::factory()->create([
            'title' => '1日前に登録',
            'created_at' => now()->subDay(),
        ]);

        Book::factory()->create([
            'title' => '本日登録',
            'created_at' => now(),
        ]);

        $response = $this->get(route('books.index', [
            'sort' => 'newest',
        ]));

        $response->assertSeeInOrder([
            '本日登録',
            '1日前に登録',
            '2日前に登録',
        ]);

        $response = $this->get(route('books.index', [
            'sort' => 'oldest',
        ]));

        $response->assertSeeInOrder([
            '2日前に登録',
            '1日前に登録',
            '本日登録',
        ]);
    }

    /**
     * 評価順に書籍一覧が表示される。
     */
    public function test_book_can_be_sorted_by_rating(): void
    {
        Book::factory()->create([
            'title' => '評価がない書籍',
        ]);

        $book1 = Book::factory()->create([
            'title' => '評価5の書籍',
        ]);

        $book2 = Book::factory()->create([
            'title' => '評価3の書籍',
        ]);

        Review::factory()->create([
            'book_id' => $book1->id,
            'rating' => 5,
        ]);

        Review::factory()->create([
            'book_id' => $book2->id,
            'rating' => 3,
        ]);

        $response = $this->get(route('books.index', [
            'sort' => 'rating',
        ]));

        $response->assertSeeInOrder([
            '評価5の書籍',
            '評価3の書籍',
            '評価がない書籍',
        ]);
    }

    /**
     * タイトル順に書籍一覧が表示される。
     */
    public function test_book_can_be_sorted_by_title(): void
    {
        Book::factory()->create([
            'title' => '書籍C',
        ]);

        Book::factory()->create([
            'title' => '書籍A',
        ]);

        Book::factory()->create([
            'title' => '書籍B',
        ]);

        $response = $this->get(route('books.index', [
            'sort' => 'title',
        ]));

        $response->assertSeeInOrder([
            '書籍A',
            '書籍B',
            '書籍C',
        ]);
    }

    /**
     * ゲストがアクセスできる。
     */
    public function test_guest_can_access_book_list(): void
    {
        $response = $this->get(route('books.index'));

        $response->assertStatus(200);
    }
}
