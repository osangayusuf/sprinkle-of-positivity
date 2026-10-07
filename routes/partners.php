<?php

use App\Http\Controllers\Partner\ParticipantController;
use App\Http\Controllers\Partner\PartnerRegistrationController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('partners/register', [PartnerRegistrationController::class, 'create'])->name('partner.register');
    Route::post('partners/register', [PartnerRegistrationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('partner.register.store');
});

Route::middleware('auth')->group(function () {
    Route::get('partners/pending', [PartnerRegistrationController::class, 'pending'])->name('partner.pending');
});

Route::middleware(['auth', 'verified', 'onboarded'])->group(function () {
    Route::get('partner/participants', [ParticipantController::class, 'index'])->name('partner.participants');
    Route::post('partner/participants/{membership}/nudge', [ParticipantController::class, 'nudge'])->name('partner.participants.nudge');
    Route::post('partner/participants/{membership}/reject', [ParticipantController::class, 'reject'])->name('partner.participants.reject');
});
