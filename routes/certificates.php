<?php

use App\Http\Controllers\CertificateController;
use Illuminate\Support\Facades\Route;

Route::get('certificates/{certificate:code}', [CertificateController::class, 'show'])
    ->name('certificates.show');

Route::middleware(['auth', 'verified', 'onboarded'])->group(function () {
    Route::get('certificates', [CertificateController::class, 'index'])->name('certificates.index');
});
