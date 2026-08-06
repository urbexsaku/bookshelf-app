<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\RedirectResponse;

class LikeController extends Controller
{
    /**
     * レビューのいいねを登録する
     */
    public function toggle(Review $review): RedirectResponse
    {
        $user = auth()->user();

        $user->likedReviews()->toggle($review->id);

        return back();
    }
}
