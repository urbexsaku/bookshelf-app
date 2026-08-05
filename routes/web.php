<?php

use App\Http\Controllers\BookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [BookController::class, 'index'])->name('books.index');

Route::middleware(['auth'])->group(function () {
    Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
    Route::post('/books', [BookController::class, 'store'])->name('books.store');
    Route::get('/books/{book}/edit', [BookController::class, 'edit'])->name('books.edit');
    Route::put('/books/{book}', [BookController::class, 'update'])->name('books.update');
    Route::delete('/books/{book}', [BookController::class, 'destroy'])->name('books.destroy');
});

Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');
Route::get('/ranking', function () {
    return view('welcome');
})->name('ranking.index');
Route::get('/favorites', function () {
    return view('welcome');
})->name('favorites.index');
Route::get('/genres', function () {
    return view('welcome');
})->name('genres.index');
Route::post('/favorites/{book}', function () {
    return back();
})->name('favorites.toggle');
Route::post('/books/{book}/reviews', function () {
    return back();
})->name('reviews.store');
Route::post('/reviews/{review}/like', function () {
    return back();
})->name('reviews.like');
Route::get('/reviews/{review}/edit', function () {
    return back();
})->name('reviews.edit');
Route::delete('/reviews/{review}', function () {
    return back();
})->name('reviews.destroy');
