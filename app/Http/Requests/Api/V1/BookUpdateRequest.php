<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookUpdateRequest extends FormRequest
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
            'isbn' => [
                'required',
                'digits:13',
                Rule::unique('books', 'isbn')
                    ->ignore($this->route('book')),
            ],
            'published_date' => ['required', 'date'],
            'genre_ids' => ['required', 'array', 'min:1'],
            'genre_ids.*' => ['integer', 'exists:genres,id'],
            'description' => ['nullable', 'string', 'max:255'],
            'image_url' => ['nullable', 'url', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'タイトルを指定してください',
            'title.string' => 'タイトルは文字列で指定してください',
            'title.max' => 'タイトルは255文字以下で指定してください',
            'author.required' => '著者名を指定してください',
            'author.string' => '著者名は文字列で指定してください',
            'author.max' => '著者名は255文字以下で指定してください',
            'isbn.required' => 'ISBNを指定してください',
            'isbn.digits' => 'ISBNは13桁の数字で指定してください',
            'isbn.unique' => 'このISBNは既に登録されています',
            'published_date.required' => '出版日を指定してください',
            'published_date.date' => '出版日は正しい日付で指定してください',
            'genre_ids.required' => 'ジャンルは1つ以上指定してください',
            'genre_ids.*.integer' => 'ジャンルIDは整数で指定してください',
            'genre_ids.*.exists' => '指定されたジャンルが存在しません',
            'description.string' => '説明は文字列で指定してください',
            'description.max' => '説明は255文字以下で指定してください',
            'image_url.url' => '画像URLはURL形式で指定してください',
            'image_url.max' => '画像URLは255文字以下で指定してください',
        ];
    }
}
