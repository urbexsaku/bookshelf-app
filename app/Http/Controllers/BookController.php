<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookRequest;
use App\Models\Book;
use App\Models\Genre;
use App\Services\GoogleBooksService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

class BookController extends Controller
{
    /**
     * 書籍一覧を表示する。
     *
     * @param  Request  $request  キーワード、ジャンル、ソート条件を含むリクエスト
     * @return View 書籍一覧画面のビュー
     */
    public function index(Request $request): View
    {
        $query = Book::with('genres');

        $keyword = $request->keyword;
        $genre = $request->genre;
        $sort = $request->sort;

        $query->keywordSearch($keyword)
            ->genreFilter($genre)
            ->sortBy($sort);

        $books = $query
            ->paginate(10)
            ->withQueryString();

        $genres = Genre::all();

        return view('books.index', compact('books', 'genres'));
    }

    /**
     * 書籍詳細画面を表示する。
     *
     * @param  Book  $book  表示する書籍
     * @return View 書籍詳細画面のビュー
     */
    public function show(Book $book): View
    {
        return view('books.show', compact('book'));
    }

    /**
     * 書籍登録画面を表示する。
     *
     * @return View 書籍登録画面のビュー
     */
    public function create(): View
    {
        $genres = Genre::all();

        return view('books.create', compact('genres'));
    }

    /**
     * 書籍を登録する。
     *
     * @param  BookRequest  $request  登録する書籍情報を含むリクエスト
     * @return RedirectResponse 登録した書籍の詳細画面へのリダイレクト
     */
    public function store(BookRequest $request): RedirectResponse
    {
        $book = Book::create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'author' => $request->author,
            'isbn' => $request->isbn,
            'published_date' => $request->published_date,
            'description' => $request->description,
            'image_url' => $request->image_url,
        ]);

        $book->genres()->attach($request->genres);

        return redirect()->route('books.show', $book)
            ->with('success', '書籍を登録しました。');
    }

    /**
     * 書籍編集画面を表示する。
     *
     * @param  Book  $book  編集する書籍
     * @return View 書籍編集画面のビュー
     */
    public function edit(Book $book): View
    {
        $this->authorize('update', $book);

        $genres = Genre::all();

        return view('books.edit', compact('book', 'genres'));
    }

    /**
     * 書籍を編集する。
     *
     * @param  BookRequest  $request  更新する書籍情報を含むリクエスト
     * @param  Book  $book  更新する書籍
     * @return RedirectResponse 更新した書籍の詳細画面へのリダイレクト
     */
    public function update(BookRequest $request, Book $book): RedirectResponse
    {
        $this->authorize('update', $book);

        $book->update($request->validated());
        $book->genres()->sync($request->genres);

        return redirect()->route('books.show', $book)
            ->with('success', '書籍を更新しました。');
    }

    /**
     * 書籍を削除する。
     *
     * @param  Book  $book  削除する書籍
     * @return RedirectResponse 書籍一覧画面へのリダイレクト
     */
    public function destroy(Book $book): RedirectResponse
    {
        $this->authorize('delete', $book);

        $book->delete();

        return redirect()->route('books.index')
            ->with('success', '書籍を削除しました。');
    }

    /**
     * ISBNから書籍情報を取得する。
     *
     * @param  string  $isbn  検索するISBN
     * @param  GoogleBooksService  $googleBooksService  Google Books APIを利用するサービス
     * @return JsonResponse 書籍情報またはエラーレスポンス
     */
    public function searchByIsbn(string $isbn, GoogleBooksService $googleBooksService): JsonResponse
    {
        try {
            $book = $googleBooksService->searchByIsbn($isbn);

            if ($book === null) {
                return response()->json([
                    'error' => '該当する書籍が見つかりません。',
                ], 404);
            }

            return response()->json($book);
        } catch (RuntimeException $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
