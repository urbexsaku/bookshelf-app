<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReadingPlanRequest extends FormRequest
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
            'book_id' => [
                Rule::requiredIf($this->isMethod('POST')),
                'integer',
                'exists:books,id',
                $this->isMethod('POST')
                    ? Rule::unique('reading_plans', 'book_id')
                        ->where(function ($query) {
                            return $query
                                ->where('user_id', auth()->id())
                                ->where('status', 'in_progress');
                        })
                    : null,
            ],
            'target_date' => ['required', 'date', 'after_or_equal:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'book_id.required' => '書籍を選択してください。',
            'book_id.unique' => 'この書籍には現在実行中の読書計画が登録されています。',
            'target_date.required' => '期日を入力してください。',
            'target_date.date' => '期日は正しい日付で入力してください。',
            'target_date.after_or_equal' => '期日は本日以降の日付で入力してください。',
        ];
    }
}
