<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GoogleBooksService
{
    /**
     * ISBNからGoogle Books APIで書籍情報を取得する
     */
    public function searchByIsbn(string $isbn): array
    {
        $response = Http::get(
            'https://www.googleapis.com/books/v1/volumes',
            [
                'q' => "isbn:{$isbn}",
                'key' => config('services.google_books.api_key'),
            ]
        );

        if ($response->failed()) {
            throw new RuntimeException('Google Books APIへの接続に失敗しました。');
        }

        $data = $response->json();

        if (empty($data['items'])) {
            throw new RuntimeException('該当する書籍が見つかりません。');
        }

        $volumeInfo = $data['items'][0]['volumeInfo'] ?? [];

        return [
            'title' => $volumeInfo['title'] ?? null,
            'author' => isset($volumeInfo['authors'])
                ? implode(', ', $volumeInfo['authors'])
                : null,
            'published_date' => $volumeInfo['publishedDate'] ?? null,
            'description' => $volumeInfo['description'] ?? null,
            'image_url' => $volumeInfo['imageLinks']['thumbnail'] ?? null,
        ];
    }
}
