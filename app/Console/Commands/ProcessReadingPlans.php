<?php

namespace App\Console\Commands;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use App\Notifications\ReadingPlanReminder;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProcessReadingPlans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-reading-plans';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * 読書計画の期限切れ処理を行い、対象ユーザーにリマインダー通知を送信する。
     */
    public function handle(): int
    {
        // 期限切れ計画のexpired化処理
        ReadingPlan::query()
            ->where('status', ReadingPlanStatus::InProgress)
            ->whereDate('target_date', '<', Carbon::today())
            ->update([
                'status' => ReadingPlanStatus::Expired,
            ]);

        $today = now()->startOfDay();
        $threeDaysLater = $today->copy()->addDays(3);
        $threeDaysAgo = $today->copy()->subDays(3);

        // 3日前リマインダー通知処理
        $threeDaysBeforePlans = ReadingPlan::where('status', ReadingPlanStatus::InProgress)
            ->whereDate('target_date', $threeDaysLater)
            ->get();

        foreach ($threeDaysBeforePlans as $readingPlan) {
            $readingPlan->user->notify(
                new ReadingPlanReminder($readingPlan, 'three_days_before')
            );
        }

        // 当日リマインダー通知処理
        $onDueDatePlans = ReadingPlan::where('status', ReadingPlanStatus::InProgress)
            ->whereDate('target_date', $today)
            ->get();

        foreach ($onDueDatePlans as $readingPlan) {
            $readingPlan->user->notify(
                new ReadingPlanReminder($readingPlan, 'on_due_date')
            );
        }

        // 3日後再エンゲージメント通知処理
        $threeDaysAfterPlans = ReadingPlan::where('status', ReadingPlanStatus::Expired)
            ->whereDate('target_date', $threeDaysAgo)
            ->get();

        foreach ($threeDaysAfterPlans as $readingPlan) {
            $readingPlan->user->notify(
                new ReadingPlanReminder($readingPlan, 'three_days_after')
            );
        }

        return self::SUCCESS;
    }
}
