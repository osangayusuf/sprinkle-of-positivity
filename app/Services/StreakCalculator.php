<?php

namespace App\Services;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class StreakCalculator
{
    /**
     * Work out a member's progress through a challenge from the dates on which
     * they completed a day.
     *
     * A missed day ends the member's run and progress starts again from day
     * one. A run that is still alive is not broken just because today's day
     * is not done yet: the member has until the end of today to post.
     *
     * Only `$requiredDays` (day numbers that had a verse) can be missed; when
     * null every calendar day counts. Days before `$countsFromDay` (when the
     * member joined) are ignored, and days before `$forgiveBeforeDay` can be
     * completed but never break the run. A run's span counts the days without
     * a verse inside it, so a skipped day never costs a member progress.
     *
     * @param  Collection<int, CarbonInterface|string>  $completedDates
     * @param  array<int, int>|null  $requiredDays
     * @return array{completed_days: array<int, int>, completed_count: int, current_streak: int, longest_streak: int, completed_today: bool, longest_span: int, run_day: int, reset_days: array<int, int>}
     */
    public function calculate(
        Collection $completedDates,
        CarbonInterface $startsOn,
        int $durationDays,
        CarbonInterface $today,
        ?array $requiredDays = null,
        int $countsFromDay = 1,
        int $forgiveBeforeDay = 1,
    ): array {
        $start = Carbon::parse($startsOn)->startOfDay();
        $todayDay = (int) $start->diffInDays(Carbon::parse($today)->startOfDay(), false) + 1;

        $completed = $completedDates
            ->map(fn ($date) => (int) $start->diffInDays(Carbon::parse($date)->startOfDay(), false) + 1)
            ->filter(fn (int $day) => $day >= 1 && $day <= $durationDays && $day <= $todayDay)
            ->unique()
            ->sort()
            ->values();

        $completedToday = $todayDay >= 1 && $completed->contains($todayDay);

        $days = collect($requiredDays ?? range(1, max(0, min($todayDay, $durationDays))))
            ->merge($completed)
            ->filter(fn (int $day) => $day >= max(1, $countsFromDay) && $day <= $todayDay)
            ->unique()
            ->sort()
            ->values();

        $run = 0;
        $runStart = null;
        $longestStreak = 0;
        $longestSpan = 0;
        $resetDays = [];

        foreach ($days as $day) {
            if ($completed->contains($day)) {
                $runStart ??= $day;
                $run++;
                $longestStreak = max($longestStreak, $run);
                $longestSpan = max($longestSpan, $day - $runStart + 1);

                continue;
            }

            if ($day === $todayDay || $day < $forgiveBeforeDay) {
                continue;
            }

            if ($run > 0) {
                $resetDays[] = $day;
            }

            $run = 0;
            $runStart = null;
        }

        return [
            'completed_days' => $completed->all(),
            'completed_count' => $completed->count(),
            'current_streak' => $run,
            'longest_streak' => $longestStreak,
            'completed_today' => $completedToday,
            'longest_span' => $longestSpan,
            'run_day' => $runStart === null ? 1 : max(1, $todayDay - $runStart + 1),
            'reset_days' => $resetDays,
        ];
    }
}
