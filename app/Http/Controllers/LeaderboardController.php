<?php

namespace App\Http\Controllers;

use App\Enums\GroupMembershipRole;
use App\Enums\GroupMembershipStatus;
use App\Http\Resources\LeaderboardEntryResource;
use App\Models\GroupMembership;
use App\Models\Role;
use App\Models\User;
use App\Services\ChallengeProgress;
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
            $entry->setAttribute('rank', $index + 1);
            $entry->setAttribute('progress', min(1, $entry->points / $topPoints));
            $entry->setAttribute('is_me', $entry->id === $user->id);

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
     * Who is keeping their run going, which partners' participants are most
     * consistent, and (for partners and admins only) who is falling behind.
     */
    public function consistency(Request $request, ChallengeProgress $challengeProgress): Response
    {
        $user = $request->user();
        $isAdmin = $user->hasRole(Role::ADMIN);
        $isPartner = $user->isApprovedPartner();

        $memberships = GroupMembership::query()
            ->with(['user', 'group', 'partner'])
            ->where('role', GroupMembershipRole::Member->value)
            ->where('status', GroupMembershipStatus::Approved->value)
            ->whereHas('group', fn ($groups) => $groups->whereNotNull('starts_on')->whereNotNull('duration_days'))
            ->get();

        $progress = $challengeProgress->forMemberships($memberships);

        $rows = $memberships
            ->filter(fn (GroupMembership $membership) => $progress->has($membership->id))
            ->map(fn (GroupMembership $membership) => [
                'membership' => $membership,
                'progress' => $progress[$membership->id],
            ]);

        $entries = $rows
            ->sortBy([
                fn ($a, $b) => $b['progress']['current_streak'] <=> $a['progress']['current_streak'],
                fn ($a, $b) => $b['progress']['completed_count'] <=> $a['progress']['completed_count'],
                fn ($a, $b) => $a['membership']->user_id <=> $b['membership']->user_id,
            ])
            ->take(self::MAX_ENTRIES)
            ->values()
            ->map(fn (array $row, int $index) => [
                'rank' => $index + 1,
                'name' => $row['membership']->user->name,
                'avatar' => $row['membership']->user->avatar,
                'group' => $row['membership']->group->name,
                'current_streak' => $row['progress']['current_streak'],
                'run_day' => $row['progress']['run_day'],
                'is_me' => $row['membership']->user_id === $user->id,
            ]);

        $partners = $rows
            ->filter(fn ($row) => $row['membership']->partner !== null)
            ->groupBy(fn ($row) => $row['membership']->partner_id)
            ->map(function ($partnerRows) {
                $total = $partnerRows->count();
                $onTrack = $partnerRows->filter(fn ($row) => $row['progress']['status'] !== 'lagging')->count();

                return [
                    'name' => $partnerRows->first()['membership']->partner->name,
                    'avatar' => $partnerRows->first()['membership']->partner->avatar,
                    'participants' => $total,
                    'on_track' => $onTrack,
                    'percent' => (int) round($onTrack / $total * 100),
                ];
            })
            ->sortBy([['percent', 'desc'], ['participants', 'desc'], ['name', 'asc']])
            ->values();

        $lagging = ($isAdmin || $isPartner)
            ? $rows
                ->filter(fn ($row) => $row['progress']['status'] === 'lagging')
                ->filter(fn ($row) => $isAdmin || $row['membership']->partner_id === $user->id)
                ->map(fn ($row) => [
                    'name' => $row['membership']->user->name,
                    'group' => $row['membership']->group->name,
                    'partner' => $row['membership']->partner?->name,
                    'reset_count' => $row['progress']['reset_count'],
                ])
                ->sortByDesc('reset_count')
                ->values()
            : null;

        return Inertia::render('leaderboard/consistency', [
            'entries' => $entries,
            'partners' => $partners,
            'lagging' => $lagging,
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
