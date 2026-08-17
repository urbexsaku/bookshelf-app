<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreShowTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    /**
     * ジャンル詳細が表示される
     */
    public function test_genre_detail_displays_related_books(): void
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

        $response = $this->actingAs($this->user)->get(route('genres.show', $genre));

        $response->assertStatus(200);
        $response->assertSee('テストジャンル');
        $response->assertSee('テスト書籍');
        $response->assertDontSee('別ジャンルの書籍');
    }

    /**
     * ゲストがアクセスできない
     */
    public function test_guest_cannot_access_genre_detail_page(): void
    {
        $genre = Genre::create([
            'name' => 'テストジャンル',
        ]);

        $book = Book::factory()->create();

        $genre->books()->attach($book->id);

        $response = $this->get(route('genres.show', $genre));

        $response->assertRedirect('/login');
    }
}
