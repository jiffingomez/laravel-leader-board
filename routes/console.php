<?php

use App\Models\LeaderBoard;
use App\Models\WinnerBoard;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


Schedule::call(function (){
    $leader = new \App\Http\Controllers\LeaderBoardController();
    $leader->winner();
})->everyMinute();
