<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewLikeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        foreach (Review::all() as $review) {
            $likerCount = rand(0, 3);

            $likerIds = $users
                ->where('id', '!=', $review->user_id)
                ->random($likerCount)
                ->pluck('id')
                ->all();

            $review->likedByUsers()->syncWithoutDetaching($likerIds);
        }
    }
}
