<?php

namespace App\Http\Controllers;

use App\Services\LevelResolver;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * The mobile profile overview: points, level, and account links.
     */
    public function show(Request $request, LevelResolver $levels): Response
    {
        $user = $request->user();
        $level = $levels->forPoints($user->points);

        return Inertia::render('profile/index', [
            'points' => $user->points,
            'level' => $level['label'] ?? null,
        ]);
    }
}
