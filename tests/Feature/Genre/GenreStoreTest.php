<?php

namespace Tests\Feature;

use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreStoreTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    /**
     * ジャンルを登録できる
     */
    public function test_user_can_register_genre(): void
    {
        $response = $this->actingAs($this->user)->post(route('genres.store'), [
            'name' => 'テストジャンル',
        ]);

        $response->assertRedirect(route('genres.index'));

        $this->assertDatabaseHas('genres', [
            'name' => 'テストジャンル',
        ]);
    }

    /**
     * ジャンル名が未入力の場合、バリデーションメッセージが表示される
     */
    public function test_validation_message_is_displayed_when_name_is_empty(): void
    {
        $response = $this->actingAs($this->user)->post(route('genres.store'), [
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
            'name' => 'テストジャンル',
        ]);

        $response = $this->actingAs($this->user)->post(route('genres.store'), [
            'name' => 'テストジャンル',
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
    public function test_guest_cannot_access_genre_registration_page(): void
    {
        $response = $this->get(route('genres.create'));

        $response->assertRedirect('/login');
    }

    /**
     * ゲストがジャンル登録できない
     */
    public function test_guest_cannot_register_genre(): void
    {
        $response = $this->post(route('genres.store'), [
            'name' => 'テストジャンル',
        ]);

        $response->assertRedirect('/login');

        $this->assertDatabaseMissing('genres', [
            'name' => 'テストジャンル',
        ]);
    }
}
