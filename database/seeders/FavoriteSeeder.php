<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class FavoriteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $favorites = [
            1 => [1, 2, 3, 4],
            2 => [5, 6, 7, 8],
            3 => [9, 10, 11],
            4 => [1, 2, 3, 4, 5],
            5 => [6, 7, 8, 9, 10],
        ];

        foreach ($favorites as $userId => $bookIds) {
            $user = User::find($userId);

            $user->favoriteBooks()->syncWithoutDetaching($bookIds);
        }
    }
}
