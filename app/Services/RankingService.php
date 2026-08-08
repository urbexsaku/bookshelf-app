<?php

namespace App\Services;

use App\Models\Book;
use Illuminate\Database\Eloquent\Collection;

class RankingService
{
    /**
     * ランキングを取得する
     */
    public function getRanking(): Collection
    {
        return Book::query()
            ->whereHas('reviews')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->orderByDesc('reviews_avg_rating')
            ->limit(10)
            ->get();
    }
}
