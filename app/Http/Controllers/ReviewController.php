<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewRequest;
use App\Models\Book;
use App\Models\Review;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ReviewController extends Controller
{
    /**
     * レビューを投稿する。
     *
     * @param  ReviewRequest  $request  投稿するレビュー情報を含むリクエスト
     * @param  Book  $book  レビューを投稿する書籍
     * @return RedirectResponse 書籍詳細画面へのリダイレクト
     */
    public function store(ReviewRequest $request, Book $book): RedirectResponse
    {
        Review::create([
            'user_id' => auth()->id(),
            'book_id' => $book->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return back()
            ->with('success', 'レビューを投稿しました。');
    }

    /**
     * レビュー編集画面を表示する。
     *
     * @param  Review  $review  編集するレビュー
     * @return View レビュー編集画面のビュー
     */
    public function edit(Review $review): View
    {
        $this->authorize('update', $review);

        return view('reviews.edit', compact('review'));
    }

    /**
     * レビューを編集する。
     *
     * @param  ReviewRequest  $request  編集するレビュー情報を含むリクエスト
     * @return RedirectResponse 書籍詳細画面へのリダイレクト
     */
    public function update(ReviewRequest $request, Review $review): RedirectResponse
    {
        $this->authorize('update', $review);

        $review->update($request->validated());

        return redirect()->route('books.show', $review->book)
            ->with('success', 'レビューを更新しました。');
    }

    /**
     * レビューを削除する。
     *
     * @param  Review  $review  削除するレビュー
     * @return RedirectResponse 書籍詳細画面へのリダイレクト
     */
    public function destroy(Review $review): RedirectResponse
    {
        $this->authorize('delete', $review);

        $book = $review->book;
        $review->delete();

        return redirect()->route('books.show', $book)
            ->with('success', 'レビューを削除しました。');
    }
}
