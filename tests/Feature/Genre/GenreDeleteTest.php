<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreDeleteTest extends TestCase
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
     * ジャンルを削除できる
     */
    public function test_user_can_delete_genre(): void
    {
        $response = $this->actingAs($this->user)->delete(route('genres.destroy', $this->genre));

        $response->assertRedirect(route('genres.index'));

        $this->assertDatabaseMissing('genres', [
            'id' => $this->genre->id,
        ]);
    }

    /**
     * 書籍に紐づいたジャンルは削除できない
     */
    public function test_user_cannot_delete_genre_related_to_book(): void
    {
        $book = Book::factory()->create();

        $this->genre->books()->attach($book->id);

        $response = $this->actingAs($this->user)->delete(route('genres.destroy', $this->genre));

        $response->assertRedirect(route('genres.index'));

        $response->assertSessionHas(
            'error',
            '書籍が紐づいているジャンルは削除できません'
        );

        $this->assertDatabaseHas('genres', [
            'id' => $this->genre->id,
        ]);
    }

    /**
     * ゲストがジャンルを削除できない
     */
    public function test_guest_cannot_delete_genre(): void
    {
        $response = $this->delete(route('genres.destroy', $this->genre));

        $response->assertRedirect('/login');

        $this->assertDatabaseHas('genres', [
            'id' => $this->genre->id,
        ]);
    }
}
