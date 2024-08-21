<?php

use App\Http\Controllers\LeaderBoardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/home', function () {
    return view('leader_board');
});

Route::get('leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard');
