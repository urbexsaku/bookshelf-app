<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class BookStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'isbn' => ['required', 'digits:13', 'unique:books,isbn'],
            'published_date' => ['required', 'date'],
            'genres' => ['required', 'array', 'min: 1'],
            'description' => ['nullable', 'max:255'],
            'image_url' => ['nullable', 'url', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'タイトルを入力してください',
            'title.string' => 'タイトルは文字列で入力してください',
            'title.max' => 'タイトルは255文字以下で入力してください',
            'author.required' => '著者名を入力してください',
            'author.string' => '著者名は文字列で入力してください',
            'author.max' => '著者名は255文字以下で入力してください',
            'isbn.required' => 'ISBNを入力してください',
            'isbn.digits' => 'ISBNは13桁の数字で入力してください',
            'isbn.unique' => 'このISBNは既に登録されています',
            'published_date.required' => '出版日を入力してください',
            'published_date.string' => '出版日は正しい日付で入力してください',
            'genres.required' => 'ジャンルは1つ以上選択してください',
            'description.max' => '説明は255文字以下で入力してください',
            'image_url.url' => '画像URLはURL形式で入力してください',
            'image_url.max' => '画像URLは255文字以下で入力してください',
        ];
    }
}
