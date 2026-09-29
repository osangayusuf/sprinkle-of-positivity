<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return request()->user() ? to_route('home') : Inertia::render('landing');
})->name('root');

Route::get('testimonials', fn () => Inertia::render('testimonials'))->name('testimonials');

Route::middleware(['auth', 'verified', 'onboarded'])->group(function () {
    Route::get('home', [HomeController::class, 'show'])->name('home');
});

require __DIR__.'/onboarding.php';
require __DIR__.'/groups.php';
require __DIR__.'/admin.php';
require __DIR__.'/notifications.php';
require __DIR__.'/leaderboard.php';
require __DIR__.'/marketplace.php';
require __DIR__.'/certificates.php';
require __DIR__.'/settings.php';
