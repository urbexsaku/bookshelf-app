<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected User $user1;
    protected User $user2;
    protected Book $book;
    protected Review $review;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user1 = User::factory()->create();
        $this->user2 = User::factory()->create();
        $this->book = Book::factory()->create();
        $this->review = Review::factory()->create([
            'user_id' => $this->user1->id,
            'book_id' => $this->book->id,
            'rating' => 1,
            'comment' => 'テストレビューコメント',
        ]);
    }

    /**
     * レビューを更新できる
     */
    public function test_user_can_update_review(): void
    {
        $response = $this->actingAs($this->user1)->put(route('reviews.update', $this->review), [
            'rating' => 5,
            'comment' => '更新テストレビューコメント',
        ]);

        $response->assertRedirect(route('books.show', $this->book));

        $this->assertDatabaseHas('reviews', [
            'id' => $this->review->id,
            'rating' => 5,
            'comment' => '更新テストレビューコメント',
        ]);
    }

    /**
     * コメントが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_validation_message_is_displayed_when_comment_is_empty(): void
    {
        $response = $this->actingAs($this->user1)->put(route('reviews.update', $this->review), [
            'rating' => 5,
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
        $response = $this->actingAs($this->user1)->put(route('reviews.update', $this->review), [
            'rating' => null,
            'comment' => '更新テストレビューコメント',
        ]);

        $response->assertSessionHasErrors('rating');

        $this->assertEquals(
            '評価を選択してください',
            session('errors')->first('rating')
        );
    }

    /**
     * ゲストがレビューを更新できない
     */
    public function test_guest_cannot_update_review(): void
    {
        $response = $this->put(route('reviews.update', $this->review), [
            'rating' => 5,
            'comment' => '更新テストレビューコメント',
        ]);

        $response->assertRedirect('/login');

        $this->assertDatabaseMissing('reviews', [
            'id' => $this->review->id,
            'comment' => '更新テストレビューコメント',
        ]);
    }

    /**
     * 他人のレビューは更新できない
     */
    public function test_review_posted_by_others_cannot_be_updated(): void
    {
        $response = $this->actingAs($this->user2)->put(route('reviews.update', $this->review), [
            'rating' => 5,
            'comment' => '更新テストレビューコメント',
        ]);

        $response->assertForbidden();

        $this->assertDatabaseMissing('reviews', [
            'id' => $this->review->id,
            'comment' => '更新テストレビューコメント',
        ]);
    }
}
