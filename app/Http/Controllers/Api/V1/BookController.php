<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\BookIndexRequest;
use App\Http\Requests\Api\V1\BookRequest;
use App\Http\Resources\BookDetailResource;
use App\Http\Resources\BookIndexResource;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class BookController extends Controller
{
    /**
     * 書籍一覧を取得する。
     *
     * @param  BookIndexRequest  $request  検索・ページネーション条件を含むリクエスト
     * @return AnonymousResourceCollection 書籍一覧のリソースコレクション
     */
    public function index(BookIndexRequest $request): AnonymousResourceCollection
    {
        $query = Book::query()
            ->with('genres')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews');

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;

            $query->where(function ($query) use ($keyword) {
                $query->where('title', 'like', "%{$keyword}%")
                    ->orWhere('author', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('genre_id')) {
            $query->whereHas('genres', function ($query) use ($request) {
                $query->where('genres.id', $request->genre_id);
            });
        }

        $perPage = $request->input('per_page', 20);

        $books = $query->paginate($perPage);

        return BookIndexResource::collection($books);
    }

    /**
     * 書籍を登録する。
     *
     * @param  BookRequest  $request  登録する書籍情報を含むリクエスト
     * @return JsonResponse 登録した書籍のJSONレスポンス
     */
    public function store(BookRequest $request): JsonResponse
    {
        $book = $request->user()
            ->books()
            ->create($request->safe()->except('genre_ids'));

        $book->genres()->sync($request->genre_ids);

        $book->load(['genres', 'reviews.user'])
            ->loadAvg('reviews', 'rating')
            ->loadCount('reviews');

        return (new BookDetailResource($book))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * 書籍詳細を取得する。
     *
     * @param  Book  $book  取得する書籍
     * @return BookDetailResource 書籍詳細のリソース
     */
    public function show(Book $book): BookDetailResource
    {
        $book->load(['genres', 'reviews.user'])
            ->loadAvg('reviews', 'rating')
            ->loadCount('reviews');

        return new BookDetailResource($book);
    }

    /**
     * 書籍情報を更新する。
     *
     * @param  BookRequest  $request  更新する書籍情報を含むリクエスト
     * @param  Book  $book  更新する書籍
     * @return BookDetailResource 更新した書籍詳細のリソース
     */
    public function update(BookRequest $request, Book $book): BookDetailResource
    {
        $this->authorize('update', $book);

        $book->update($request->safe()->except('genre_ids'));
        $book->genres()->sync($request->genre_ids);

        $book->load(['genres', 'reviews.user'])
            ->loadAvg('reviews', 'rating')
            ->loadCount('reviews');

        return new BookDetailResource($book);
    }

    /**
     * 書籍情報を削除する。
     *
     * @param  Book  $book  削除する書籍
     * @return Response コンテンツなしのレスポンス
     */
    public function destroy(Book $book): Response
    {
        $this->authorize('delete', $book);

        $book->delete();

        return response()->noContent();
    }
}
