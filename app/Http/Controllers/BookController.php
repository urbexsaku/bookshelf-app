<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookStoreRequest;
use App\Http\Requests\BookUpdateRequest;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class BookController extends Controller
{
    /**
     * 書籍一覧を表示する
     */
    public function index(): View
    {
        $books = Book::paginate(10);

        return view('books.index', compact('books'));
    }

    /**
     * 書籍詳細画面を表示する
     */
    public function show(Book $book): View
    {
        return view('books.show', compact('book'));
    }

    /**
     * 書籍登録画面を表示する
     */
    public function create(): View
    {
        $genres = Genre::all();

        return view('books.create', compact('genres'));
    }

    /**
     * 書籍を登録する
     */
    public function store(BookStoreRequest $request): RedirectResponse
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
            ->with('success', '書籍を登録しました');
    }

    /**
     * 書籍編集画面を表示する
     */
    public function edit(Book $book): View
    {
        $this->authorize('update', $book);

        $genres = Genre::all();

        return view('books.edit', compact('book', 'genres'));
    }

    /**
     * 書籍を編集する
     */
    public function update(BookUpdateRequest $request, Book $book): RedirectResponse
    {
        $this->authorize('update', $book);

        $book->update($request->validated());
        $book->genres()->sync($request->genres);

        return redirect()->route('books.show', $book)
            ->with('success', '書籍を更新しました');
    }

    /**
     * 書籍を削除する
     */
    public function destroy(Book $book): RedirectResponse
    {
        $this->authorize('delete', $book);

        $book->delete();

        return redirect()->route('books.index')
            ->with('success', '書籍を削除しました');
    }
}
