<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    /**
     * お気に入り書籍一覧画面を表示する。
     *
     * @return View お気に入り書籍一覧画面のビュー
     */
    public function index(): View
    {
        $user = auth()->user();
        $books = $user->favoriteBooks()->paginate(10);

        return view('favorites.index', compact('books'));
    }

    /**
     * 書籍をお気に入り登録・解除を切り替える。
     *
     * @param  Book  $book  お気に入り登録・解除する書籍
     * @return RedirectResponse 元の画面へのリダイレクト
     */
    public function toggle(Book $book): RedirectResponse
    {
        $user = auth()->user();

        $user->favoriteBooks()->toggle($book->id);

        return back();
    }
}
