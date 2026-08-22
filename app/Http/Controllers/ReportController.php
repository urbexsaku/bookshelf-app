<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * 読書レポートを表示する
     */
    public function index(): View
    {
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
                ->join('books', 'reviews.book_id', '=', 'books.id')
                ->join('book_genre', 'books.id', '=', 'book_genre.book_id')
                ->join('genres', 'book_genre.genre_id', '=', 'genres.id')
                ->select(
                    'genres.id',
                    'genres.name',
                    DB::raw('AVG(reviews.rating) as average_rating'),
                    DB::raw('COUNT(reviews.id) as count')
                )
                ->groupBy('genres.id', 'genres.name')
                ->orderByDesc('average_rating')
                ->limit(5)
                ->get(),
        ];

        return view('reports.index', compact('stats'));
    }
}
