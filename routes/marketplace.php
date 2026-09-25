<?php

use App\Http\Controllers\MarketplaceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'onboarded'])->group(function () {
    Route::get('marketplace', [MarketplaceController::class, 'index'])->name('marketplace.index');
});
