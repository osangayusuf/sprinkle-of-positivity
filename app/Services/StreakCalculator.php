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
     * A streak that is still alive is not broken just because today's day is
     * not done yet: it is counted back from yesterday until they post.
     *
     * @param  Collection<int, CarbonInterface|string>  $completedDates
     * @return array{completed_days: array<int, int>, completed_count: int, current_streak: int, longest_streak: int, completed_today: bool}
     */
    public function calculate(Collection $completedDates, CarbonInterface $startsOn, int $durationDays, CarbonInterface $today): array
    {
        $start = Carbon::parse($startsOn)->startOfDay();
        $todayDay = (int) $start->diffInDays(Carbon::parse($today)->startOfDay(), false) + 1;

        $completed = $completedDates
            ->map(fn ($date) => (int) $start->diffInDays(Carbon::parse($date)->startOfDay(), false) + 1)
            ->filter(fn (int $day) => $day >= 1 && $day <= $durationDays && $day <= $todayDay)
            ->unique()
            ->sort()
            ->values();

        $completedToday = $completed->contains($todayDay);

        $cursor = min($todayDay, $durationDays);

        if (! $completedToday && $todayDay <= $durationDays) {
            $cursor--;
        }

        $currentStreak = 0;

        while ($cursor >= 1 && $completed->contains($cursor)) {
            $currentStreak++;
            $cursor--;
        }

        $longestStreak = 0;
        $run = 0;
        $previous = null;

        foreach ($completed as $day) {
            $run = $previous !== null && $day === $previous + 1 ? $run + 1 : 1;
            $longestStreak = max($longestStreak, $run);
            $previous = $day;
        }

        return [
            'completed_days' => $completed->all(),
            'completed_count' => $completed->count(),
            'current_streak' => $currentStreak,
            'longest_streak' => $longestStreak,
            'completed_today' => $completedToday,
        ];
    }
}
