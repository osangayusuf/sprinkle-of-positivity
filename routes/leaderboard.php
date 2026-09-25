<?php

use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'onboarded'])->group(function () {
    Route::get('leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard.index');
    Route::get('leaderboard/levels', [LeaderboardController::class, 'levels'])->name('leaderboard.levels');

    Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
});
