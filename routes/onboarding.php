<?php

use App\Http\Controllers\OnboardingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('onboarding', [OnboardingController::class, 'edit'])->name('onboarding.edit');
    Route::patch('onboarding', [OnboardingController::class, 'update'])->name('onboarding.update');
});
