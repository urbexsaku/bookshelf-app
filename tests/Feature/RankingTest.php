<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\UserSeeder::class);
        $this->seed(\Database\Seeders\BookSeeder::class);

        $this->user = User::factory()->create();
    }

    /**
     * 正しいランキング情報が表示される
     */
    public function test_ranking_is_displayed_in_descending_order(): void
    {
        $book1 = Book::findOrFail(1);
        $book2 = Book::findOrFail(2);
        $book3 = Book::findOrFail(3);

        // 上位3冊にレビュー評価を登録
        Review::factory()->create([
            'book_id' => $book1->id,
            'rating' => 5,
        ]);

        Review::factory()->create([
            'book_id' => $book2->id,
            'rating' => 4,
        ]);

        Review::factory()->create([
            'book_id' => $book3->id,
            'rating' => 3,
        ]);

        // 4～11位の書籍にレビュー評価1を付ける
        foreach (range(4, 11) as $bookId) {
            Review::factory()->create([
                'book_id' => $bookId,
                'rating' => 1,
            ]);
        }

        $response = $this->actingAs($this->user)->get(route('ranking.index'));

        $response->assertStatus(200);

        // 上位3冊が評価順に表示されることを確認
        $response->assertViewHas('rankedBooks', function ($rankedBooks) {
            return $rankedBooks->pluck('id')->take(3)->values()->all() === [1, 2, 3]
                && $rankedBooks[0]->reviews_avg_rating == 5
                && $rankedBooks[1]->reviews_avg_rating == 4
                && $rankedBooks[2]->reviews_avg_rating == 3;
        });

        // 上位10冊だけ表示されていることを確認
        $response->assertViewHas('rankedBooks', function ($rankedBooks) {
            return $rankedBooks->count() === 10;
        });
    }

    /**
     * ゲストがアクセスできる
     */
    public function test_guest_can_access_ranking_page(): void
    {
        $response = $this->get(route('ranking.index'));

        $response->assertStatus(200);
    }
}
