<?php

namespace App\Http\Controllers;

use App\Http\Resources\LeaderboardEntryResource;
use App\Models\User;
use App\Services\LevelResolver;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeaderboardController extends Controller
{
    private const MAX_ENTRIES = 50;

    /**
     * The platform-wide points leaderboard.
     */
    public function index(Request $request, LevelResolver $levels): Response
    {
        $user = $request->user();

        $ranked = User::query()
            ->orderByDesc('points')
            ->orderBy('id')
            ->limit(self::MAX_ENTRIES)
            ->get();

        $topPoints = $ranked->max('points') ?: 1;

        $entries = $ranked->values()->map(function (User $entry, int $index) use ($topPoints, $user) {
            $entry->rank = $index + 1;
            $entry->progress = min(1, $entry->points / $topPoints);
            $entry->is_me = $entry->id === $user->id;

            return $entry;
        });

        $myLevel = $levels->forPoints($user->points);

        return Inertia::render('leaderboard/index', [
            'entries' => LeaderboardEntryResource::collection($entries),
            'myPoints' => $user->points,
            'myLevel' => $myLevel['label'] ?? null,
        ]);
    }

    /**
     * The static list of level tiers and their point thresholds.
     */
    public function levels(Request $request, LevelResolver $levels): Response
    {
        return Inertia::render('leaderboard/levels', [
            'levels' => $levels->all(),
            'myPoints' => $request->user()->points,
        ]);
    }
}
