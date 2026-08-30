<?php

namespace Tests\Feature\Plan;

use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanDeleteTest extends TestCase
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
     * 読書計画を削除できる。
     */
    public function test_user_can_delete_plan(): void
    {
        $response = $this->actingAs($this->user1)->delete(route('reading-plans.destroy', $this->plan));

        $response->assertRedirect(route('reading-plans.index'));

        $this->assertDatabaseMissing('reading_plans', [
            'id' => $this->plan->id,
            'user_id' => $this->user1->id,
        ]);
    }

    /**
     * ゲストが読書計画を削除できない。
     */
    public function test_guest_cannot_delete_plan(): void
    {
        $response = $this->delete(route('reading-plans.destroy', $this->plan));

        $response->assertRedirect('/login');

        $this->assertDatabaseHas('reading_plans', [
            'id' => $this->plan->id,
            'user_id' => $this->user1->id,
        ]);
    }

    /**
     * 他人の計画は削除できない。
     */
    public function test_plan_created_by_others_cannot_be_deleted(): void
    {
        $response = $this->actingAs($this->user2)->delete(route('reading-plans.destroy', $this->plan));

        $response->assertForbidden();

        $this->assertDatabaseHas('reading_plans', [
            'id' => $this->plan->id,
            'user_id' => $this->user1->id,
        ]);
    }
}
