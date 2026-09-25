<?php

use App\Http\Controllers\BibleVerseLookupController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\GroupApplicationController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\GroupManagementController;
use App\Http\Controllers\GroupVerseController;
use App\Http\Controllers\InsightController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\QuizResponseController;
use App\Http\Controllers\ReactionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'onboarded'])->group(function () {
    Route::get('groups', [GroupController::class, 'index'])->name('groups.index');
    Route::get('groups/{group:slug}', [GroupController::class, 'show'])->name('groups.show');
    Route::get('groups/{group:slug}/join', [GroupApplicationController::class, 'create'])->name('groups.join');
    Route::post('groups/{group:slug}/apply', [GroupApplicationController::class, 'store'])->name('groups.apply');

    Route::get('groups/{group:slug}/manage', [GroupManagementController::class, 'edit'])->name('groups.manage');
    Route::patch('groups/{group:slug}/applications/{membership}', [GroupApplicationController::class, 'update'])->name('groups.applications.update');
    Route::delete('groups/{group:slug}/members/{membership}', [GroupApplicationController::class, 'destroy'])->name('groups.members.destroy');

    Route::get('groups/{group:slug}/verse', [GroupVerseController::class, 'show'])->name('groups.verse.show');
    Route::get('groups/{group:slug}/verse/edit', [GroupVerseController::class, 'edit'])->name('groups.verse.edit');
    Route::put('groups/{group:slug}/verse', [GroupVerseController::class, 'update'])->name('groups.verse.update');

    Route::get('groups/{group:slug}/insights/create', [InsightController::class, 'create'])->name('groups.insights.create');
    Route::post('groups/{group:slug}/insights', [InsightController::class, 'store'])->name('groups.insights.store');
    Route::get('groups/{group:slug}/insights/{insight}', [InsightController::class, 'show'])->name('groups.insights.show');
    Route::post('groups/{group:slug}/insights/{insight}/comments', [CommentController::class, 'store'])->name('groups.insights.comments.store');
    Route::post('groups/{group:slug}/insights/{insight}/reactions', [ReactionController::class, 'forInsight'])->name('groups.insights.reactions.store');
    Route::post('groups/{group:slug}/insights/{insight}/comments/{comment}/reactions', [ReactionController::class, 'forComment'])->name('groups.insights.comments.reactions.store');

    Route::post('groups/{group:slug}/quizzes', [QuizController::class, 'store'])->name('groups.quizzes.store');
    Route::get('groups/{group:slug}/quizzes/{quiz}/edit', [QuizController::class, 'edit'])->name('groups.quizzes.edit');
    Route::put('groups/{group:slug}/quizzes/{quiz}', [QuizController::class, 'update'])->name('groups.quizzes.update');
    Route::delete('groups/{group:slug}/quizzes/{quiz}', [QuizController::class, 'destroy'])->name('groups.quizzes.destroy');
    Route::post('groups/{group:slug}/quizzes/{quiz}/responses', [QuizResponseController::class, 'store'])->name('groups.quizzes.responses.store');

    Route::get('bible-verse-lookup', BibleVerseLookupController::class)->name('bible-verse-lookup');
});
