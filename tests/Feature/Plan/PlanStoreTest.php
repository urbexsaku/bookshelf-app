<?php

namespace Tests\Feature\Plan;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanStoreTest extends TestCase
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
     * 読書計画を作成できる。
     */
    public function test_user_can_create_reading_plan(): void
    {
        $response = $this->actingAs($this->user)->post(route('reading-plans.store'), [
            'book_id' => $this->book->id,
            'target_date' => today()->addDays(3),
        ]);

        $response->assertRedirect(route('reading-plans.index'));

        $this->assertDatabaseHas('reading_plans', [
            'book_id' => $this->book->id,
            'user_id' => $this->user->id,
        ]);
    }

    /**
     * 書籍が未選択の場合、バリデーションメッセージが表示される。
     */
    public function test_validation_message_is_displayed_when_book_is_not_selected(): void
    {
        $response = $this->actingAs($this->user)->post(route('reading-plans.store'), [
            'book_id' => null,
            'target_date' => today()->addDays(3),
        ]);

        $response->assertSessionHasErrors('book_id');

        $this->assertEquals(
            '書籍を選択してください。',
            session('errors')->first('book_id')
        );
    }

    /**
     * 期日が未選択の場合、バリデーションメッセージが表示される。
     */
    public function test_validation_message_is_displayed_when_targer_date_is_not_selected(): void
    {
        $response = $this->actingAs($this->user)->post(route('reading-plans.store'), [
            'book_id' => $this->book->id,
            'target_date' => null,
        ]);

        $response->assertSessionHasErrors('target_date');

        $this->assertEquals(
            '期日を入力してください。',
            session('errors')->first('target_date')
        );
    }

    /**
     * 期日を過去の日付で選択した場合、バリデーションメッセージが表示される。
     */
    public function test_validation_message_is_displayed_when_targer_date_is_in_past(): void
    {
        $response = $this->actingAs($this->user)->post(route('reading-plans.store'), [
            'book_id' => $this->book->id,
            'target_date' => today()->subDays(3),
        ]);

        $response->assertSessionHasErrors('target_date');

        $this->assertEquals(
            '期日は本日以降の日付で入力してください。',
            session('errors')->first('target_date')
        );
    }

    /**
     * ゲストがアクセスできない。
     */
    public function test_guest_cannot_access_plan_creation_page(): void
    {
        $response = $this->get(route('reading-plans.store'));

        $response->assertRedirect('/login');
    }

    /**
     * ゲストが読書計画を作成できない。
     */
    public function test_guest_cannot_create_pplan(): void
    {
        $response = $this->post(route('reading-plans.store'), [
            'book_id' => $this->book->id,
            'target_date' => today()->addDays(3),
        ]);

        $response->assertRedirect('/login');

        $this->assertDatabaseMissing('reading_plans', [
            'book_id' => $this->book->id,
            'user_id' => $this->user->id,
        ]);
    }
}
