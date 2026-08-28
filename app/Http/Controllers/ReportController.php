<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * 読書レポートを表示する。
     *
     * @return View 読書レポート画面のビュー
     */
    public function index(): View
    {
        /** @var User $user */
        $user = auth()->user();

        $stats = [

            // 基本サマリ―（総レビュー数、読冊数、平均評価点）
            'summary' => [
                'total_reviews' => $user->reviews()->count(),
                'books_read' => $user->reviews()->count(),
                'average_rating' => $user->reviews()->avg('rating') ?? 0,
            ],

            // ユーザーのレビュー件数を評価点数ごとに集計
            'rating_distribution' => $user->reviews()
                ->selectRaw('rating, COUNT(*) as count')
                ->groupBy('rating')
                ->pluck('count', 'rating'),

            // 評価4以上の書籍を評価が高い順に最大5件表示
            'top_rated_books' => $user->reviews()
                ->with('book')
                ->where('rating', '>=', 4)
                ->orderByDesc('rating')
                ->limit(5)
                ->get()
                ->map(function ($review) {
                    return [
                        'id' => $review->book->id,
                        'title' => $review->book->title,
                        'author' => $review->book->author,
                        'rating' => $review->rating,
                    ];
                }),

            // ジャンルごとの平均評価点・評価件数を、平均評価が高い順に最大5件表示
            'genre_ratings' => $user->reviews()
                ->with('book.genres')
                ->get()
                ->flatMap(function ($review) {
                    return $review->book->genres->map(function ($genre) use ($review) {
                        return [
                            'id' => $genre->id,
                            'name' => $genre->name,
                            'rating' => $review->rating,
                        ];
                    });
                })
                ->groupBy('id')
                ->map(function ($reviews) {
                    return [
                        'id' => $reviews->first()['id'],
                        'name' => $reviews->first()['name'],
                        'average_rating' => $reviews->avg('rating'),
                        'count' => $reviews->count(),
                    ];
                })
                ->sortByDesc('average_rating')
                ->take(5)
                ->values(),
        ];

        return view('reports.index', compact('stats'));
    }
}
