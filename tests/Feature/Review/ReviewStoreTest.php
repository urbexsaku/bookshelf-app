<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewStoreTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Book $book;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->book = Book::factory()->create();
    }

    /**
     * レビューを投稿できる
     */
    public function test_user_can_post_review(): void
    {
        $response = $this->actingAs($this->user)
            ->from(route('books.show', $this->book))
            ->post(route('reviews.store', $this->book), [
                'user_id' => $this->user->id,
                'book_id' => $this->book->id,
                'rating' => 1,
                'comment' => 'テストレビューコメント',
            ]);

        $response->assertRedirect(route('books.show', $this->book));

        $response->assertSessionHas(
            'success',
            'レビューを投稿しました'
        );

        $this->assertDatabaseHas('reviews', [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'rating' => 1,
            'comment' => 'テストレビューコメント',
        ]);
    }

    /**
     * コメントが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_validation_message_is_displayed_when_comment_is_empty(): void
    {
        $response = $this->actingAs($this->user)->post(route('reviews.store', $this->book), [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'rating' => 1,
            'comment' => '',
        ]);

        $response->assertSessionHasErrors('comment');

        $this->assertEquals(
            'コメントを入力してください',
            session('errors')->first('comment')
        );
    }

    /**
     * 評価が未選択の場合、バリデーションメッセージが表示される
     */
    public function test_validation_message_is_displayed_when_rating_is_empty(): void
    {
        $response = $this->actingAs($this->user)->post(route('reviews.store', $this->book), [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'rating' => null,
            'comment' => 'テストレビューコメント',
        ]);

        $response->assertSessionHasErrors('rating');

        $this->assertEquals(
            '評価を選択してください',
            session('errors')->first('rating')
        );
    }

    /**
     * ゲストはレビュー投稿できない
     */
    public function test_guest_cannot_post_review(): void
    {
        $response = $this->post(route('reviews.store', $this->book), [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'rating' => 1,
            'comment' => 'テストレビューコメント',
        ]);

        $response->assertRedirect('/login');

        $this->assertDatabaseMissing('reviews', [
            'comment' => 'テストレビューコメント',
        ]);
    }
}
