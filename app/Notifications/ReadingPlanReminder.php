<?php

namespace App\Notifications;

use App\Models\ReadingPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReadingPlanReminder extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public ReadingPlan $readingPlan, public string $timing) {}

    /**
     * 通知をデータベースに送る
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * 通知の表示内容
     */
    public function toDatabase(object $notifiable): array
    {
        $title = match ($this->timing) {
            'three_days_before' => '読書期日が近づいています',
            'on_due_date' => '読書期日は今日です',
            'three_days_after' => '読書期日を過ぎています',
        };

        $body = match ($this->timing) {
            'three_days_before' => "「{$this->readingPlan->book->title}」の読書期日まであと3日です。",
            'on_due_date' => "「{$this->readingPlan->book->title}」の読書期日は今日です。",
            'three_days_after' => "「{$this->readingPlan->book->title}」の読書期日を3日過ぎています。",
        };

        return [
            'reading_plan_id' => $this->readingPlan->id,
            'timing' => $this->timing,
            'title' => $title,
            'body' => $body,
        ];
    }
}
