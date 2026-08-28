<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenreRequest;
use App\Models\Genre;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class GenreController extends Controller
{
    /**
     * ジャンル一覧を表示する。
     *
     * @return View ジャンル一覧画面のビュー
     */
    public function index(): View
    {
        $genres = Genre::withCount('books')->get();

        return view('genres.index', compact('genres'));
    }

    /**
     * ジャンル詳細画面を表示する。
     *
     * @param  Genre  $genre  表示するジャンル
     * @return View ジャンル詳細画面のビュー
     */
    public function show(Genre $genre): View
    {
        $books = $genre->books()->paginate(10);

        return view('genres.show', compact('genre', 'books'));
    }

    /**
     * ジャンル登録画面を表示する。
     *
     * @return View ジャンル登録画面のビュー
     */
    public function create(): View
    {
        return view('genres.create');
    }

    /**
     * ジャンルを登録する。
     *
     * @param  GenreRequest  $request  登録するジャンル情報を含むリクエスト
     * @return RedirectResponse ジャンル一覧画面へのリダイレクト
     */
    public function store(GenreRequest $request): RedirectResponse
    {
        Genre::create(['name' => $request->name]);

        return redirect()->route('genres.index')
            ->with('success', 'ジャンルを登録しました。');
    }

    /**
     * ジャンル編集画面を表示する。
     *
     * @param  Genre  $genre  編集するジャンル
     * @return View ジャンル編集画面のビュー
     */
    public function edit(Genre $genre): View
    {
        return view('genres.edit', compact('genre'));
    }

    /**
     * ジャンルを編集する。
     *
     * @param  GenreRequest  $request  更新するジャンル情報を含むリクエスト
     * @param  Genre  $genre  更新するジャンル
     * @return RedirectResponse ジャンル一覧画面へのリダイレクト
     */
    public function update(GenreRequest $request, Genre $genre): RedirectResponse
    {
        $genre->update($request->validated());

        return redirect()->route('genres.index')
            ->with('success', 'ジャンルを更新しました。');
    }

    /**
     * ジャンルを削除する
     *
     * @param  Genre  $genre  削除するジャンル
     * @return RedirectResponse ジャンル一覧画面へのリダイレクト
     */
    public function destroy(Genre $genre): RedirectResponse
    {
        if ($genre->books()->exists()) {
            return redirect()->route('genres.index')
                ->with('error', '書籍が紐づいているジャンルは削除できません。');
        }

        $genre->delete();

        return redirect()->route('genres.index')
            ->with('success', 'ジャンルを削除しました。');
    }
}
