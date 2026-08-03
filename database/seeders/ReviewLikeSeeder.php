<?php

namespace Database\Seeders;

use App\Models\Review;
use Illuminate\Database\Seeder;

class ReviewLikeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $likes = [
            1  => [2, 3],
            2  => [1],
            3  => [1, 4, 5],
            4  => [1, 2],
            5  => [3],
            6  => [4, 5],
            7  => [],
            8  => [1, 2],
            9  => [3],
            10 => [],
            11 => [4, 5],
            12 => [1, 4, 5],
            13 => [1, 2],
            14 => [3],
            15 => [],
            16 => [4, 5],
            17 => [1, 4, 5],
            18 => [1, 2],
            19 => [3],
            20 => [],
            21 => [4, 5],
            22 => [1, 4, 5],
            23 => [1, 2],
            24 => [3],
            25 => [],
            26 => [4, 5],
            27 => [1, 4, 5],
            28 => [1, 2],
            29 => [3],
            30 => [4],
            31 => [2, 3, 4],
            32 => [],
        ];

        foreach ($likes as $reviewId => $userIds) {
            $review = Review::find($reviewId);

            $review->likedUsers()->syncWithoutDetaching($userIds);
        }
    }
}
