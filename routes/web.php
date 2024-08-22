<?php

use App\Http\Controllers\LeaderBoardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('leader_board');
});
