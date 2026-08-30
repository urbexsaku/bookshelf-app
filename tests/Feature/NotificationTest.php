<?php

namespace Tests\Feature;

use App\Models\ReadingPlan;
use App\Models\User;
use App\Notifications\ReadingPlanReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    /**
     * 期日翌日に読書計画が自動で期限切れになる。
     */
    public function test_plan_is_expired_automatically(): void
    {
        $plan = ReadingPlan::factory()->create([
            'user_id' => $this->user->id,
            'target_date' => today()->subDay(),
        ]);

        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'status' => 'in_progress',
        ]);

        $this->artisan('app:process-reading-plans');

        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'status' => 'expired',
        ]);
    }

    /**
     * 期日3日前の読書計画に通知が送信される。
     */
    public function test_notification_is_sent_three_days_before_target_date(): void
    {
        ReadingPlan::factory()->create([
            'user_id' => $this->user->id,
            'target_date' => today()->addDays(3),
        ]);

        $this->artisan('app:process-reading-plans');

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $this->user->id,
            'type' => ReadingPlanReminder::class,
        ]);

        $notification = $this->user->notifications()->first();

        $this->assertEquals('three_days_before', $notification->data['timing']);
    }

    /**
     * 期日当日の読書計画に通知が送信される。
     */
    public function test_notification_is_sent_on_target_date(): void
    {
        ReadingPlan::factory()->create([
            'user_id' => $this->user->id,
            'target_date' => today(),
        ]);

        $this->artisan('app:process-reading-plans');

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $this->user->id,
            'type' => ReadingPlanReminder::class,
        ]);

        $notification = $this->user->notifications()->first();

        $this->assertEquals('on_due_date', $notification->data['timing']);
    }

    /**
     * 期日3日後の読書計画に通知が送信される。
     */
    public function test_notification_is_sent_three_days_after_target_date(): void
    {
        ReadingPlan::factory()->create([
            'user_id' => $this->user->id,
            'target_date' => today()->subDays(3),
        ]);

        $this->artisan('app:process-reading-plans');

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $this->user->id,
            'type' => ReadingPlanReminder::class,
        ]);

        $notification = $this->user->notifications()->first();

        $this->assertEquals('three_days_after', $notification->data['timing']);
    }

    /**
     * 通知一覧画面に通知情報が表示される。
     */
    public function test_notification_is_displayed_in_notification_list(): void
    {
        $threeDaysLaterPlan = ReadingPlan::factory()->create([
            'user_id' => $this->user->id,
            'target_date' => today()->addDays(3),
        ]);

        $onDueDatePlan = ReadingPlan::factory()->create([
            'user_id' => $this->user->id,
            'target_date' => today(),
        ]);

        $threeDaysAfterPlan = ReadingPlan::factory()->create([
            'user_id' => $this->user->id,
            'target_date' => today()->subDays(3),
        ]);

        $this->artisan('app:process-reading-plans');

        $response = $this->actingAs($this->user)->get(route('notifications.index'));

        $response->assertStatus(200);
        $response->assertSee('読書期日が近づいています');
        $response->assertSee("「{$threeDaysLaterPlan->book->title}」の読書期日まであと3日です。");
        $response->assertSee('読書期日は今日です');
        $response->assertSee("「{$onDueDatePlan->book->title}」の読書期日は今日です。");
        $response->assertSee('読書期日を過ぎています');
        $response->assertSee("「{$threeDaysAfterPlan->book->title}」の読書期日を3日過ぎています。");
    }

    /**
     * 通知を既読にできる。
     */
    public function test_user_can_make_notification_read(): void
    {
        $plan = ReadingPlan::factory()->create([
            'user_id' => $this->user->id,
            'target_date' => today()->addDays(3),
        ]);

        $this->user->notify(new ReadingPlanReminder(
            $plan,
            'three_days_before'
        ));

        $notification = $this->user->notifications()->first();

        $response = $this->actingAs($this->user)->post(route('notifications.read', $notification->id));

        $response->assertRedirect(route('notifications.index'));

        $this->assertNotNull(
            $this->user->notifications()->find($notification->id)->read_at
        );

        $response = $this->actingAs($this->user)->get(
            route('notifications.index')
        );

        $response->assertStatus(200);
        $response->assertDontSee('既読にする');
    }

    /**
     * ゲストがアクセスできない。
     */
    public function test_guest_cannot_access_notification_page(): void
    {
        $response = $this->get(route('notifications.index'));

        $response->assertRedirect('/login');
    }

    /**
     * ゲストが通知を既読できない。
     */
    public function test_guest_cannot_make_notification_read(): void
    {
        $plan = ReadingPlan::factory()->create([
            'user_id' => $this->user->id,
            'target_date' => today()->addDays(3),
        ]);

        $this->user->notify(new ReadingPlanReminder(
            $plan,
            'three_days_before'
        ));

        $notification = $this->user->notifications()->first();

        $response = $this->post(route('notifications.read', $notification->id));

        $response->assertRedirect('/login');

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'read_at' => null,
        ]);
    }

    /**
     * 他人の通知は既読にできない。
     */
    public function test_notification_cannot_be_made_read_by_others(): void
    {
        $otherUser = User::factory()->create();

        $plan = ReadingPlan::factory()->create([
            'user_id' => $this->user->id,
            'target_date' => today()->addDays(3),
        ]);

        $this->user->notify(new ReadingPlanReminder(
            $plan,
            'three_days_before'
        ));

        $notification = $this->user->notifications()->first();

        $this->actingAs($otherUser)
            ->post(route('notifications.read', $notification->id))
            ->assertNotFound();

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'read_at' => null,
        ]);
    }
}
