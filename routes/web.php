<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ManualController;
use App\Http\Controllers\SeoController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return request()->user() ? to_route('home') : Inertia::render('landing');
})->name('root');

Route::get('robots.txt', [SeoController::class, 'robots'])->name('robots');
Route::get('sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');

Route::get('testimonials', fn () => Inertia::render('testimonials'))->name('testimonials');
Route::get('accountability-partners', function () {
    $partners = collect(File::files(public_path('images/accountability-partners')))
        ->map(fn ($file) => [
            'name' => $file->getFilenameWithoutExtension(),
            'photo' => '/images/accountability-partners/'.rawurlencode($file->getFilename()),
        ])
        ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
        ->values();

    return Inertia::render('accountability-partners', ['partners' => $partners]);
})->name('accountability-partners');
Route::get('hangout', function () {
    $directory = public_path('images/hangout/thumb');

    $photos = collect(File::isDirectory($directory) ? File::files($directory) : [])
        ->map(fn ($file) => [
            'name' => $file->getFilename(),
            'thumb' => '/images/hangout/thumb/'.rawurlencode($file->getFilename()),
            'full' => '/images/hangout/full/'.rawurlencode($file->getFilename()),
        ])
        ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
        ->values();

    return Inertia::render('hangout', ['photos' => $photos]);
})->name('hangout');
Route::get('manual', ManualController::class)->name('manual');
Route::get('support', fn () => Inertia::render('support'))->name('support');

Route::middleware('onboarded')->group(function () {
    Route::get('home', [HomeController::class, 'show'])->name('home');
});

Route::middleware(['auth', 'verified', 'onboarded'])->group(function () {
    Route::inertia('give', 'give/index')->name('give');
});

require __DIR__.'/onboarding.php';
require __DIR__.'/partners.php';
require __DIR__.'/groups.php';
require __DIR__.'/admin.php';
require __DIR__.'/notifications.php';
require __DIR__.'/leaderboard.php';
require __DIR__.'/marketplace.php';
require __DIR__.'/certificates.php';
require __DIR__.'/settings.php';
