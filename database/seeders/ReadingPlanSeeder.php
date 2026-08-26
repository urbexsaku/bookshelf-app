<?php

namespace Database\Seeders;

use App\Models\ReadingPlan;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ReadingPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $readingPlans = [
            [
                'user_id' => 1,
                'book_id' => 1,
                'status' => 'in_progress',
                'target_date' => Carbon::today()->addDays(3),
            ],
            [
                'user_id' => 1,
                'book_id' => 2,
                'status' => 'in_progress',
                'target_date' => Carbon::today(),
            ],
            [
                'user_id' => 1,
                'book_id' => 3,
                'status' => 'in_progress',
                'target_date' => Carbon::today()->subDays(3),
            ],
            [
                'user_id' => 1,
                'book_id' => 4,
                'status' => 'in_progress',
                'target_date' => Carbon::today()->addDays(7),
            ],
            [
                'user_id' => 1,
                'book_id' => 5,
                'status' => 'completed',
                'target_date' => Carbon::today()->subDays(10),
                'completed_at' => Carbon::today()->subDays(5),
            ],
            [
                'user_id' => 2,
                'book_id' => 6,
                'status' => 'in_progress',
                'target_date' => Carbon::today()->addDays(5),
            ],
        ];

        foreach ($readingPlans as $readingPlan) {
            ReadingPlan::create($readingPlan);
        }
    }
}
