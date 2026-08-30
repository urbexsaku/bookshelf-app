<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_reading_report(): void
    {
        $user = User::factory()->create();

        $genre1 = Genre::create(['name' => '小説']);
        $genre2 = Genre::create(['name' => 'ミステリー']);
        $genre3 = Genre::create(['name' => 'ビジネス']);
        $genre4 = Genre::create(['name' => 'エッセイ']);

        $books = collect(range(1, 7))->map(
            fn ($number) => Book::factory()->create([
                'title' => "書籍{$number}",
            ])
        );

        $genre1->books()->attach([$books[0]->id, $books[1]->id]);
        $genre2->books()->attach([$books[2]->id, $books[3]->id]);
        $genre3->books()->attach([$books[4]->id, $books[5]->id]);
        $genre4->books()->attach($books[6]->id);

        $ratings = [5, 5, 4, 4, 4, 4, 3];

        foreach ($ratings as $index => $rating) {
            Review::factory()->create([
                'user_id' => $user->id,
                'book_id' => $books[$index]->id,
                'rating' => $rating,
            ]);
        }

        $response = $this->actingAs($user)->get(route('reports.index'));
        $response->assertStatus(200);

        $stats = $response->viewData('stats');

        // 基本サマリー
        $this->assertSame(7, $stats['summary']['total_reviews']);
        $this->assertSame(7, $stats['summary']['books_read']);
        $this->assertEquals(4.1, $stats['summary']['average_rating']);

        // 評価分布
        $this->assertSame(2, $stats['rating_distribution'][5]); // 5点
        $this->assertSame(4, $stats['rating_distribution'][4]); // 4点
        $this->assertSame(1, $stats['rating_distribution'][3]); // 3点

        // TOP5書籍
        $response->assertSeeInOrder([
            '書籍1',
            '書籍2',
            '書籍3',
            '書籍4',
            '書籍5',
        ]);

        // ジャンル別評価
        $genreRatings = $stats['genre_ratings'];

        $this->assertSame('小説', $genreRatings[0]['name']);
        $this->assertSame('ミステリー', $genreRatings[1]['name']);
        $this->assertSame('ビジネス', $genreRatings[2]['name']);
        $this->assertSame('エッセイ', $genreRatings[3]['name']);

        $this->assertEquals(5.0, $genreRatings[0]['average_rating']);
        $this->assertEquals(4.0, $genreRatings[1]['average_rating']);
        $this->assertEquals(4.0, $genreRatings[2]['average_rating']);
        $this->assertEquals(3.0, $genreRatings[3]['average_rating']);
    }

    /**
     * ゲストがアクセスできない。
     */
    public function test_guest_cannot_access_report_page(): void
    {
        $response = $this->get(route('reports.index'));

        $response->assertRedirect('/login');
    }
}
