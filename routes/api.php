<?php

use App\Http\Controllers\LeaderBoardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard');
Route::get('leader/points/reset', [LeaderboardController::class, 'reset_points'])->name('reset_points');
Route::get('leader/scores', [LeaderboardController::class, 'grouped_by_scores'])->name('grouped_by_scores');
Route::get('leader/show/{id}', [LeaderboardController::class, 'show'])->name('show');
Route::post('leader/create', [LeaderboardController::class, 'store'])->name('store');
Route::post('leader/point', [LeaderboardController::class, 'point_update'])->name('point_update');
Route::delete('leader/remove/{id}', [LeaderBoardController::class, 'destroy'])->name('leader_remove');
Route::post('leader/qr', [LeaderBoardController::class, 'generate_qr_code'])->name('generate_qr_code');
Route::post('winner', [LeaderBoardController::class, 'winner'])->name('winner');
