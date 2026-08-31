<?php

namespace App\Http\Requests;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
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
                'exists:books,id'
            ],
            'target_date' => ['required', 'date', 'after_or_equal:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'book_id.required' => '書籍を選択してください。',
            'book_id.exists' => '指定された書籍が存在しません。',
            'target_date.required' => '期日を入力してください。',
            'target_date.date' => '期日は正しい日付で入力してください。',
            'target_date.after_or_equal' => '期日は本日以降の日付で入力してください。',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // 新規作成の場合
            if ($this->isMethod('POST')) {
                $exists = ReadingPlan::query()
                    ->where('user_id', auth()->id())
                    ->where('book_id', $this->book_id)
                    ->where('status', ReadingPlanStatus::InProgress)
                    ->exists();

                if ($exists) {
                    $validator->errors()->add(
                        'book_id',
                        'この書籍には現在実行中の読書計画が登録されています。',
                    );
                }
            }

            // 更新の場合
            if ($this->isMethod('PUT')) {
                $readingPlan = $this->route('reading_plan');

                if (
                    $readingPlan->status === ReadingPlanStatus::Expired
                    && ReadingPlan::query()
                        ->where('user_id', auth()->id())
                        ->where('book_id', $readingPlan->book_id)
                        ->where('status', ReadingPlanStatus::InProgress)
                        ->where('id', '!=', $readingPlan->id)
                        ->exists()
                ) {
                    $validator->errors()->add(
                        'target_date',
                        'この書籍には現在実行中の読書計画が登録されています。',
                    );
                }
            }
        });
    }
}
