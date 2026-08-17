<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreIndexTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    /**
     * ジャンル一覧が表示される
     */
    public function test_genre_list_displays_genre_information(): void
    {
        $genre = Genre::create([
            'name' => 'テストジャンル',
        ]);

        Book::factory()->count(5)->create()->each(function (Book $book) use ($genre) {
            $genre->books()->attach($book->id);
        });

        $response = $this->actingAs($this->user)->get(route('genres.index'));

        $response->assertStatus(200);
        $response->assertSee('テストジャンル');
        $response->assertSee('5冊');
    }

    /**
     * ゲストがアクセスできない
     */
    public function test_guest_cannot_access_genre_list_page(): void
    {
        $response = $this->get(route('genres.index'));

        $response->assertRedirect('/login');
    }
}
