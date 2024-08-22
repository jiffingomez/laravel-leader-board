<?php

use Illuminate\Support\Facades\Schedule;
use App\Http\Controllers\LeaderBoardController;

/**
 * Scheduling a job that will pick winner from leaderboard and insert to winnerboard table
 */
Schedule::call(function (){
    $leader = new LeaderBoardController();
    $leader->winner();
})->everyMinute();
