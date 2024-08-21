<?php

namespace Database\Seeders;

use App\Models\LeaderBoard;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LeaderBoardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        LeaderBoard::factory()->count(5)->create();
    }
}
