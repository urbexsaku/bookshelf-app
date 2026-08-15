<?php

namespace Tests\Unit\Models;

use App\Models\Book;
use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Genreに紐づくBookを取得できる
     */
    public function test_genre_can_get_related_books(): void
    {
        $book = Book::factory()->create();

        $genre = Genre::create([
            'name' => 'テストジャンル',
        ]);

        $genre->books()->attach($book->id);

        $this->assertTrue($genre->books->contains($book));
    }
}
