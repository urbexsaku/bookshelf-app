<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\RedirectResponse;

class LikeController extends Controller
{
    /**
     * レビューのいいねを登録・解除する。
     *
     * @param  Review  $review  いいね登録・解除するレビュー
     * @return RedirectResponse 元の画面へのリダイレクト
     */
    public function toggle(Review $review): RedirectResponse
    {
        $user = auth()->user();

        $user->likedReviews()->toggle($review->id);

        return back();
    }
}
