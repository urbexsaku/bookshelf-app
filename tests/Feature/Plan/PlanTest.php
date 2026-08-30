<?php

namespace Tests\Feature\Plan;

use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Book $book;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->book = Book::factory()->create([
            'title' => 'テスト書籍',
        ]);
    }

    /**
     * 読書計画一覧に書籍名・期日・状態が表示される。
     */
    public function test_plan_list_displays_plan_information(): void
    {
        ReadingPlan::create([
            'book_id' => $this->book->id,
            'user_id' => $this->user->id,
            'target_date' => '2026-01-01',
        ]);

        $response = $this->actingAs($this->user)->get(route('reading-plans.index'));

        $response->assertStatus(200);
        $response->assertSee('テスト書籍');
        $response->assertSee('2026-01-01');
        $response->assertSee('実行中');
    }

    /**
     * 状態で指定した書籍一覧が取得できる。
     */
    public function test_book_can_be_filtered_by_status(): void
    {
        $completedBook = Book::factory()->create(['title' => '読了書籍']);
        $expiredBook = Book::factory()->create(['title' => '期限切れ書籍']);

        ReadingPlan::factory()->create([
            'book_id' => $this->book->id,
            'user_id' => $this->user->id,
        ]);

        ReadingPlan::factory()->create([
            'book_id' => $completedBook->id,
            'user_id' => $this->user->id,
            'status' => 'completed',
        ]);

        ReadingPlan::factory()->create([
            'book_id' => $expiredBook->id,
            'user_id' => $this->user->id,
            'target_date' => today()->subDays(3),
            'status' => 'expired',
        ]);

        $response = $this->actingAs($this->user)->get(route('reading-plans.index', [
            'status' => 'in_progress',
        ]));

        $response->assertStatus(200);
        $response->assertSee('テスト書籍');
        $response->assertSee('実行中');
        $response->assertDontSee('読了書籍');
        $response->assertDontSee('期限切れ書籍');

        $response = $this->actingAs($this->user)->get(route('reading-plans.index', [
            'status' => 'completed',
        ]));

        $response->assertStatus(200);
        $response->assertSee('読了書籍');
        $response->assertSee('読了');
        $response->assertDontSee('テスト書籍');
        $response->assertDontSee('期限切れ書籍');

        $response = $this->actingAs($this->user)->get(route('reading-plans.index', [
            'status' => 'expired',
        ]));

        $response->assertStatus(200);
        $response->assertSee('期限切れ書籍');
        $response->assertSee('期限切れ');
        $response->assertDontSee('テスト書籍');
        $response->assertDontSee('読了書籍');
    }

    /**
     * 読書計画を読了できる。
     */
    public function test_user_can_complete_plan(): void
    {
        $plan = ReadingPlan::factory()->create([
            'book_id' => $this->book->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->post(route('reading-plans.complete', $plan));

        $response->assertRedirect(route('reading-plans.index'));

        $this->assertDatabaseHas('reading_plans', [
            'book_id' => $this->book->id,
            'user_id' => $this->user->id,
            'status' => 'completed',
        ]);
    }

    /**
     * ゲストがアクセスできない。
     */
    public function test_guest_cannot_access_plan_list(): void
    {
        $response = $this->get(route('reading-plans.index'));

        $response->assertRedirect('/login');
    }

    /**
     * 他人の計画は読了できない。
     */
    public function test_plan_made_by_others_cannot_be_completed(): void
    {
        $user2 = User::factory()->create();

        $plan = ReadingPlan::factory()->create([
            'book_id' => $this->book->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($user2)->post(route('reading-plans.complete', $plan));

        $response->assertForbidden();

        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'user_id' => $this->user->id,
            'status' => 'in_progress',
        ]);
    }
}
