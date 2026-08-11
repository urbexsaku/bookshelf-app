<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\BookIndexRequest;
use App\Http\Requests\Api\V1\BookStoreRequest;
use App\Http\Requests\Api\V1\BookUpdateRequest;
use App\Http\Resources\BookDetailResource;
use App\Http\Resources\BookIndexResource;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class BookController extends Controller
{
    /**
     * 書籍一覧を取得する
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
     * 書籍を登録する
     */
    public function store(BookStoreRequest $request): JsonResponse
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
     * 書籍詳細を取得する
     */
    public function show(Book $book): BookDetailResource
    {
        $book->load(['genres', 'reviews.user'])
            ->loadAvg('reviews', 'rating')
            ->loadCount('reviews');

        return new BookDetailResource($book);
    }

    /**
     * 書籍情報を更新する
     */
    public function update(BookUpdateRequest $request, Book $book): BookDetailResource
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
     * 書籍情報を策書する
     */
    public function destroy(Book $book): Response
    {
        $this->authorize('delete', $book);

        $book->delete();

        return response()->noContent();
    }
}
