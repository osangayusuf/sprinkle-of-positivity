<?php

use App\Services\StreakCalculator;
use Illuminate\Support\Carbon;

function streakFor(array $days, int $today, int $duration = 10): array
{
    $start = Carbon::parse('2026-03-01');
    $dates = collect($days)->map(fn (int $day) => $start->copy()->addDays($day - 1));

    return (new StreakCalculator)->calculate($dates, $start, $duration, $start->copy()->addDays($today - 1));
}

test('a run of consecutive days up to today is the current streak', function () {
    $result = streakFor([1, 2, 3], today: 3);

    expect($result)->toMatchArray([
        'completed_count' => 3,
        'current_streak' => 3,
        'longest_streak' => 3,
        'completed_today' => true,
    ]);
});

test('a streak is not broken while today is still to be done', function () {
    $result = streakFor([1, 2, 3], today: 4);

    expect($result['current_streak'])->toBe(3)
        ->and($result['completed_today'])->toBeFalse();
});

test('missing a full day resets the current streak but keeps the best one', function () {
    $result = streakFor([1, 2, 3, 5], today: 5);

    expect($result['current_streak'])->toBe(1)
        ->and($result['longest_streak'])->toBe(3);
});

test('a gap before yesterday means no current streak', function () {
    $result = streakFor([1, 2], today: 5);

    expect($result['current_streak'])->toBe(0)
        ->and($result['longest_streak'])->toBe(2);
});

test('days outside the challenge window are ignored', function () {
    $result = streakFor([0, 1, 11], today: 12, duration: 10);

    expect($result['completed_days'])->toBe([1]);
});

test('a finished challenge counts the run that reached its last day', function () {
    $result = streakFor([8, 9, 10], today: 14, duration: 10);

    expect($result['current_streak'])->toBe(3);
});
