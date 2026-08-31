<?php

namespace Tests\Feature\Plan;

use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected User $user1;

    protected User $user2;

    protected ReadingPlan $plan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user1 = User::factory()->create();
        $this->user2 = User::factory()->create();
        $this->plan = ReadingPlan::factory()->create(['user_id' => $this->user1->id]);
    }

    /**
     * 読書計画を更新できる。
     */
    public function test_user_can_update_plan(): void
    {
        // 実行中の計画を更新
        $response = $this->actingAs($this->user1)->put(route('reading-plans.update', $this->plan), [
            'target_date' => today()->addDays(5),
        ]);

        $response->assertRedirect(route('reading-plans.index'));

        $this->assertDatabaseHas('reading_plans', [
            'id' => $this->plan->id,
            'user_id' => $this->user1->id,
        ]);

        $this->assertEquals(
            today()->addDays(5)->toDateString(),
            $this->plan->fresh()->target_date->toDateString()
        );

        // 期限切れの計画を更新
        $expiredPlan = ReadingPlan::factory()->create([
            'user_id' => $this->user1->id,
            'target_date' => today()->subDays(3),
            'status' => 'expired',
        ]);

        $response = $this->actingAs($this->user1)->put(route('reading-plans.update', $expiredPlan), [
            'target_date' => today()->addDays(5),
        ]);

        $response->assertRedirect(route('reading-plans.index'));

        $this->assertDatabaseHas('reading_plans', [
            'id' => $expiredPlan->id,
            'user_id' => $this->user1->id,
            'status' => 'in_progress',
        ]);

        $this->assertEquals(
            today()->addDays(5)->toDateString(),
            $expiredPlan->fresh()->target_date->toDateString()
        );
    }

    /**
     * 期日が未選択の場合、バリデーションメッセージが表示される。
     */
    public function test_validation_message_is_displayed_when_target_date_is_not_selected(): void
    {
        $response = $this->actingAs($this->user1)->put(route('reading-plans.update', $this->plan), [
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
    public function test_validation_message_is_displayed_when_target_date_is_in_past(): void
    {
        $book = Book::factory()->create();

        ReadingPlan::factory()->create([
            'book_id' => $book->id,
            'user_id' => $this->user1->id,
            'target_date' => today()->addDays(3),
        ]);

        $expiredPlan = ReadingPlan::factory()->create([
            'book_id' => $book->id,
            'user_id' => $this->user1->id,
            'target_date' => today()->subDays(3),
            'status' => 'expired',
        ]);

        $response = $this->actingAs($this->user1)->put(route('reading-plans.update', $expiredPlan), [
            'target_date' => today()->addDays(3),
        ]);

        $response->assertSessionHasErrors('target_date');

        $this->assertEquals(
            'この書籍には現在実行中の読書計画が登録されています。',
            session('errors')->first('target_date')
        );
    }

    /**
     * 別の実行中の読書計画がある期限切れ計画を更新する場合、バリデーションエラーが返される。
     */
    public function test_validation_message_is_displayed_when_updating_expired_plan_with_existing_in_progress_plan(): void
    {
        $response = $this->actingAs($this->user1)->put(route('reading-plans.update', $this->plan), [
            'book_id' => 9999,
            'target_date' => today()->subDays(3),
        ]);

        $response->assertSessionHasErrors('book_id');

        $this->assertEquals(
            '指定された書籍が存在しません。',
            session('errors')->first('book_id')
        );
    }

    /**
     * ゲストがアクセスできない。
     */
    public function test_guest_cannot_access_plan_update_page(): void
    {
        $response = $this->get(route('reading-plans.edit', $this->plan));

        $response->assertRedirect('/login');
    }

    /**
     * ゲストが読書計画を更新できない。
     */
    public function test_guest_cannot_update_plan(): void
    {
        $response = $this->put(route('reading-plans.update', $this->plan), [
            'target_date' => today()->addDays(5),
        ]);

        $response->assertRedirect('/login');

        $this->assertDatabaseHas('reading_plans', [
            'id' => $this->plan->id,
            'user_id' => $this->user1->id,
        ]);

        $this->assertEquals(
            today()->addDays(3)->toDateString(),
            $this->plan->fresh()->target_date->toDateString()
        );
    }

    /**
     * 他人の計画は更新できない。
     */
    public function test_plan_created_by_others_cannot_be_updated(): void
    {
        $response = $this->actingAs($this->user2)->put(route('reading-plans.update', $this->plan), [
            'target_date' => today()->addDays(5),
        ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('reading_plans', [
            'id' => $this->plan->id,
            'user_id' => $this->user1->id,
        ]);

        $this->assertEquals(
            today()->addDays(3)->toDateString(),
            $this->plan->fresh()->target_date->toDateString()
        );
    }
}
