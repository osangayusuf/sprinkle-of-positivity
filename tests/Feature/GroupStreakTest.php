<?php

use App\Models\Group;

test('currentDayNumber is null when no challenge is configured', function () {
    $group = Group::factory()->create();

    expect($group->currentDayNumber())->toBeNull();
});

test('currentDayNumber is 1 on the day the challenge starts', function () {
    $group = Group::factory()->create(['duration_days' => 60, 'starts_on' => today()]);

    expect($group->currentDayNumber())->toBe(1);
});

test('currentDayNumber counts up as the challenge progresses', function () {
    $group = Group::factory()->create([
        'duration_days' => 60,
        'starts_on' => today()->subDays(3),
    ]);

    expect($group->currentDayNumber())->toBe(4);
});

test('currentDayNumber is null before the challenge starts', function () {
    $group = Group::factory()->create([
        'duration_days' => 60,
        'starts_on' => today()->addDay(),
    ]);

    expect($group->currentDayNumber())->toBeNull();
});

test('currentDayNumber is null once the challenge has ended', function () {
    $group = Group::factory()->create([
        'duration_days' => 60,
        'starts_on' => today()->subDays(60),
    ]);

    expect($group->currentDayNumber())->toBeNull();
});
