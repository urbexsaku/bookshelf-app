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
            'page.integer' => 'page は整数で指定してください',
            'page.min' => 'page は1以上で指定してください',
            'per_page.integer' => 'per_page は は整数で指定してください',
            'per_page.max' => 'per_page は 1以上100 以下で指定してください',
        ];
    }
}
