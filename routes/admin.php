<?php

use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\CertificateController;
use App\Http\Controllers\Admin\DailyVerseBulkController;
use App\Http\Controllers\Admin\DailyVerseController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GroupController;
use App\Http\Controllers\Admin\MarketplaceController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'onboarded', 'can:access-admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics');

        Route::get('groups', [GroupController::class, 'index'])->name('groups.index');
        Route::get('groups/create', [GroupController::class, 'create'])->name('groups.create');
        Route::post('groups', [GroupController::class, 'store'])->name('groups.store');
        Route::get('groups/{group}/edit', [GroupController::class, 'edit'])->name('groups.edit');
        Route::put('groups/{group}', [GroupController::class, 'update'])->name('groups.update');
        Route::delete('groups/{group}', [GroupController::class, 'destroy'])->name('groups.destroy');

        Route::get('daily-verse/history', [DailyVerseController::class, 'history'])->name('daily-verse.history');
        Route::get('daily-verse/bulk', [DailyVerseBulkController::class, 'edit'])->name('daily-verse.bulk.edit');
        Route::post('daily-verse/bulk', [DailyVerseBulkController::class, 'store'])->name('daily-verse.bulk.store');
        Route::get('daily-verse/{date?}', [DailyVerseController::class, 'edit'])->name('daily-verse.edit');
        Route::put('daily-verse/{date?}', [DailyVerseController::class, 'update'])->name('daily-verse.update');

        Route::get('announcements/create', [AnnouncementController::class, 'create'])->name('announcements.create');
        Route::post('announcements', [AnnouncementController::class, 'store'])->name('announcements.store');

        Route::get('marketplace', [MarketplaceController::class, 'index'])->name('marketplace.index');
        Route::get('marketplace/create', [MarketplaceController::class, 'create'])->name('marketplace.create');
        Route::post('marketplace', [MarketplaceController::class, 'store'])->name('marketplace.store');
        Route::get('marketplace/{listing}/edit', [MarketplaceController::class, 'edit'])->name('marketplace.edit');
        Route::put('marketplace/{listing}', [MarketplaceController::class, 'update'])->name('marketplace.update');
        Route::delete('marketplace/{listing}', [MarketplaceController::class, 'destroy'])->name('marketplace.destroy');

        Route::get('certificates', [CertificateController::class, 'index'])->name('certificates.index');
        Route::get('certificates/groups/{group}', [CertificateController::class, 'show'])->name('certificates.show');
        Route::post('certificates/groups/{group}/members/{user}', [CertificateController::class, 'issue'])->name('certificates.issue');
        Route::patch('certificates/{certificate}/revoke', [CertificateController::class, 'revoke'])->name('certificates.revoke');
        Route::patch('certificates/{certificate}/restore', [CertificateController::class, 'restore'])->name('certificates.restore');
        Route::patch('certificates/{certificate}/name', [CertificateController::class, 'updateName'])->name('certificates.update-name');

        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::put('users/{user}/role', [UserController::class, 'updateRole'])->name('users.update-role');
    });
