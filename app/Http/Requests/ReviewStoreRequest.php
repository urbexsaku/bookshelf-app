<?php

namespace App\Http\Requests;

use App\Models\Review;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ReviewStoreRequest extends FormRequest
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
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'rating.required' => '評価を選択してください',
            'rating.integer' => '評価は整数で入力してください',
            'rating.between' => '評価は1～5の間で入力してください',
            'comment.required' => 'コメントを入力してください',
            'comment.string' => 'コメントは文字列で入力してください',
            'comment.max' => 'コメントは255文字以下で入力してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $exists = Review::where('book_id', $this->route('book')->id)
                ->where('user_id', auth()->id())
                ->exists();

            if ($exists) {
                $validator->errors()->add(
                    'comment',
                    'この書籍には既にレビューを投稿しています',
                );
            }
        });
    }
}
