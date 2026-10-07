<?php

namespace App\Services;

use App\Models\Group;
use App\Models\GroupMembership;
use App\Models\GroupVerse;
use App\Models\Insight;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ChallengeProgress
{
    public function __construct(private StreakCalculator $streaks) {}

    /**
     * One member's progress through the group's challenge, or null when the
     * group has no challenge configured.
     *
     * @return array{completed_days: array<int, int>, completed_count: int, current_streak: int, longest_streak: int, completed_today: bool, longest_span: int, reset_days: array<int, int>, reset_count: int, run_day: int, required_days: array<int, int>, missing_days: array<int, int>, eligible: bool}|null
     */
    public function forMember(Group $group, User $user): ?array
    {
        return $this->forMembers($group, [$user->id])[$user->id] ?? null;
    }

    /**
     * Progress for several members at once, keyed by user id. Runs a fixed
     * number of queries however many members there are.
     *
     * A day is completed by posting an insight on the group's verse for that
     * day. Days on which the manager set no verse cannot be completed by
     * anyone, so they are neither required nor able to break a run. Missing
     * a verse day sends the member back to day one; a member is eligible for
     * a certificate once they have completed a full challenge's worth of days
     * in a row, even past the group's original end date.
     *
     * @param  array<int, int>  $userIds
     * @return array<int, array{completed_days: array<int, int>, completed_count: int, current_streak: int, longest_streak: int, completed_today: bool, longest_span: int, reset_days: array<int, int>, reset_count: int, run_day: int, required_days: array<int, int>, missing_days: array<int, int>, eligible: bool}>
     */
    public function forMembers(Group $group, array $userIds): array
    {
        $endsOn = $group->challengeEndsOn();

        if (! $group->starts_on || ! $group->duration_days || ! $endsOn) {
            return [];
        }

        $startsOn = $group->starts_on->startOfDay();

        $verseDates = GroupVerse::query()
            ->where('group_id', $group->id)
            ->whereDate('date', '>=', $startsOn->toDateString())
            ->whereDate('date', '<=', max($endsOn, today())->toDateString())
            ->pluck('date', 'id');

        $requiredDays = $verseDates
            ->map(fn ($date) => (int) $startsOn->diffInDays($date->startOfDay()) + 1)
            ->unique()
            ->sort()
            ->values()
            ->all();

        $insights = Insight::query()
            ->where('verseable_type', (new GroupVerse)->getMorphClass())
            ->whereIn('verseable_id', $verseDates->keys())
            ->whereIn('user_id', $userIds)
            ->get(['user_id', 'verseable_id'])
            ->groupBy('user_id');

        $joinedOn = GroupMembership::query()
            ->where('group_id', $group->id)
            ->whereIn('user_id', $userIds)
            ->pluck('decided_at', 'user_id');

        $goLive = config('challenge.recalibration_starts_on');
        $forgiveBeforeDay = $goLive
            ? max(1, (int) $startsOn->diffInDays(Carbon::parse($goLive)->startOfDay(), false) + 1)
            : 1;
        $horizon = max($group->duration_days, (int) $startsOn->diffInDays(today(), false) + 1);

        $progress = [];

        foreach ($userIds as $userId) {
            $dates = ($insights[$userId] ?? collect())
                ->map(fn (Insight $insight) => $verseDates[$insight->verseable_id])
                ->values();

            $joined = $joinedOn[$userId] ?? null;
            $countsFromDay = $joined
                ? max(1, (int) $startsOn->diffInDays(Carbon::parse($joined)->startOfDay(), false) + 1)
                : 1;

            $result = $this->streaks->calculate(
                $dates,
                $startsOn,
                $horizon,
                today(),
                $requiredDays,
                $countsFromDay,
                $forgiveBeforeDay,
            );
            $missing = array_values(array_diff($requiredDays, $result['completed_days']));

            $progress[$userId] = $result + [
                'reset_count' => count($result['reset_days']),
                'required_days' => $requiredDays,
                'missing_days' => $missing,
                'eligible' => $requiredDays !== [] && $result['longest_span'] >= $group->duration_days,
            ];
        }

        return $progress;
    }

    /**
     * Progress and today's status for memberships, which may span groups,
     * keyed by membership id. Memberships of groups with no challenge are
     * left out.
     *
     * @param  Collection<int, GroupMembership>  $memberships
     * @return Collection<int, array<string, mixed>>
     */
    public function forMemberships(Collection $memberships): Collection
    {
        $results = collect();

        foreach ($memberships->groupBy('group_id') as $groupMemberships) {
            $group = $groupMemberships->first()->group;
            $progress = $this->forMembers($group, $groupMemberships->pluck('user_id')->all());

            foreach ($groupMemberships as $membership) {
                if (isset($progress[$membership->user_id])) {
                    $results[$membership->id] = $progress[$membership->user_id] + [
                        'status' => $this->statusFor($progress[$membership->user_id]),
                    ];
                }
            }
        }

        return $results;
    }

    /**
     * Where a member stands today: done, still able to post, or behind.
     *
     * @param  array{completed_today: bool, current_streak: int}  $progress
     * @return 'on_track'|'at_risk'|'lagging'
     */
    public function statusFor(array $progress): string
    {
        return match (true) {
            $progress['completed_today'] => 'on_track',
            $progress['current_streak'] > 0 => 'at_risk',
            default => 'lagging',
        };
    }
}
