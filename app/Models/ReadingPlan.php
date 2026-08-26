<?php

namespace App\Models;

use App\Enums\ReadingPlanStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReadingPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'book_id',
        'status',
        'target_date',
        'completed_at',
    ];

    protected $casts = [
        'status' => ReadingPlanStatus::class,
        'target_date' => 'date',
        'completed_at' => 'date',
    ];

    /**
     * この読書計画に紐づく書籍
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * この読書計画に紐づくユーザー
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 指定された状態に紐づく読書計画を絞り込む。
     */
    public function scopeStatusFilter(Builder $query, ?ReadingPlanStatus $status): Builder
    {
        return $query->when(
            $status,
            function (Builder $query) use ($status) {
                return $query->where('status', $status->value);
            }
        );
    }
}
