<?php

namespace App\Services;

use App\Models\Group;
use App\Models\GroupVerse;
use App\Models\Insight;
use App\Models\User;

class ChallengeProgress
{
    public function __construct(private StreakCalculator $streaks) {}

    /**
     * One member's progress through the group's challenge, or null when the
     * group has no challenge configured.
     *
     * @return array{completed_days: array<int, int>, completed_count: int, current_streak: int, longest_streak: int, completed_today: bool, required_days: array<int, int>, missing_days: array<int, int>, eligible: bool}|null
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
     * anyone, so they are not required for a certificate.
     *
     * @param  array<int, int>  $userIds
     * @return array<int, array{completed_days: array<int, int>, completed_count: int, current_streak: int, longest_streak: int, completed_today: bool, required_days: array<int, int>, missing_days: array<int, int>, eligible: bool}>
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
            ->whereDate('date', '<=', $endsOn->toDateString())
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

        $progress = [];

        foreach ($userIds as $userId) {
            $dates = ($insights[$userId] ?? collect())
                ->map(fn (Insight $insight) => $verseDates[$insight->verseable_id])
                ->values();

            $result = $this->streaks->calculate($dates, $startsOn, $group->duration_days, today());
            $missing = array_values(array_diff($requiredDays, $result['completed_days']));

            $progress[$userId] = $result + [
                'required_days' => $requiredDays,
                'missing_days' => $missing,
                'eligible' => $requiredDays !== [] && $missing === [] && $group->challengeHasEnded(),
            ];
        }

        return $progress;
    }
}
