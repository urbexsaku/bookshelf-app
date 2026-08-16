<?php

namespace Tests\Feature;

use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreUpdateTest extends TestCase
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
     * ジャンルを更新できる
     */
    public function test_user_can_update_genre(): void
    {
        $response = $this->actingAs($this->user)->put(route('genres.update', $this->genre), [
            'name' => '更新テストジャンル',
        ]);

        $response->assertRedirect(route('genres.index'));

        $this->assertDatabaseHas('genres', [
            'id' => $this->genre->id,
            'name' => '更新テストジャンル',
        ]);
    }

    /**
     * ジャンル名が未入力の場合、バリデーションメッセージが表示される
     */
    public function test_validation_message_is_displayed_when_genre_is_empty(): void
    {
        $response = $this->actingAs($this->user)->put(route('genres.update', $this->genre), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');

        $this->assertEquals(
            'ジャンル名を入力してください',
            session('errors')->first('name')
        );
    }

    /**
     * 重複するジャンル名の場合、バリデーションメッセージが表示される
     */
    public function test_validation_message_is_displayed_when_genre_is_already_registered(): void
    {
        Genre::create([
            'name' => '更新テストジャンル',
        ]);

        $response = $this->actingAs($this->user)->put(route('genres.update', $this->genre), [
            'name' => '更新テストジャンル',
        ]);

        $response->assertSessionHasErrors('name');

        $this->assertEquals(
            'このジャンル名は既に登録されています',
            session('errors')->first('name')
        );
    }

    /**
     * ゲストがアクセスできない
     */
    public function test_guest_cannot_access_genre_edit_page(): void
    {
        $response = $this->get(route('genres.edit', $this->genre));

        $response->assertRedirect('/login');
    }

    /**
     * ゲストがジャンル登録できない
     */
    public function test_guest_cannot_update_genre(): void
    {
        $response = $this->put(route('genres.update', $this->genre), [
            'name' => '更新テストジャンル',
        ]);

        $response->assertRedirect('/login');

        $this->assertDatabaseHas('genres', [
            'id' => $this->genre->id,
            'name' => 'テストジャンル',
        ]);
    }
}
