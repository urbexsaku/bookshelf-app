<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class BookIndexRequest extends FormRequest
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
            'keyword' => ['nullable', 'string'],
            'genre_id' => ['nullable', 'integer', 'exists:genres,id'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'genre_id.integer' => 'ジャンルIDは整数で指定してください',
            'genre_id.exists' => '指定されたジャンルが存在しません',
            'page.integer' => 'pageは整数で指定してください',
            'page.min' => 'pageは1以上で指定してください',
            'per_page.integer' => 'per_pageは整数で指定してください',
            'per_page.min' => 'per_pageは1以上100以下で指定してください',
            'per_page.max' => 'per_pageは1以上100以下で指定してください',
        ];
    }
}
