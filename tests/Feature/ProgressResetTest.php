<?php

use App\Models\ProgressReset;
use App\Models\User;
use App\Notifications\ChallengeAlert;
use App\Services\ChallengeProgress;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

test('the day boundary follows West Africa Time', function () {
    $this->travelTo(Carbon::parse('2026-03-01 22:59:00', 'UTC'));
    expect(today()->toDateString())->toBe('2026-03-01');

    $this->travelTo(Carbon::parse('2026-03-01 23:00:00', 'UTC'));
    expect(today()->toDateString())->toBe('2026-03-02');
});

test('missing a verse day sends a member back to day one', function () {
    $group = challengeGroup(durationDays: 5, startedDaysAgo: 3);
    $user = User::factory()->create();
    joinAsApprovedMember($group, $user);
    completeDay($group, $user, 1);
    completeDay($group, $user, 2);

    $progress = app(ChallengeProgress::class)->forMember($group, $user);

    expect($progress['reset_days'])->toBe([3])
        ->and($progress['reset_count'])->toBe(1)
        ->and($progress['current_streak'])->toBe(0)
        ->and($progress['run_day'])->toBe(1);
});

test('today is not missed until the day ends', function () {
    $group = challengeGroup(durationDays: 5, startedDaysAgo: 2);
    $user = User::factory()->create();
    joinAsApprovedMember($group, $user);
    completeDay($group, $user, 1);
    completeDay($group, $user, 2);

    $progress = app(ChallengeProgress::class)->forMember($group, $user);

    expect($progress['reset_count'])->toBe(0)
        ->and($progress['current_streak'])->toBe(2)
        ->and($progress['run_day'])->toBe(3);
});

test('a day without a verse neither breaks a run nor counts as missed', function () {
    $group = challengeGroup(durationDays: 5, startedDaysAgo: 3, daysWithoutVerse: [2]);
    $user = User::factory()->create();
    joinAsApprovedMember($group, $user);
    completeDay($group, $user, 1);
    completeDay($group, $user, 3);

    $progress = app(ChallengeProgress::class)->forMember($group, $user);

    expect($progress['reset_count'])->toBe(0)
        ->and($progress['current_streak'])->toBe(2)
        ->and($progress['run_day'])->toBe(4);
});

test('days before a member was approved cannot be missed', function () {
    $group = challengeGroup(durationDays: 6, startedDaysAgo: 4);
    $user = User::factory()->create();
    $membership = joinAsApprovedMember($group, $user);
    $membership->decided_at = today()->subDays(2)->setTime(10, 0);
    $membership->save();
    completeDay($group, $user, 3);
    completeDay($group, $user, 4);

    $progress = app(ChallengeProgress::class)->forMember($group, $user);

    expect($progress['reset_count'])->toBe(0)
        ->and($progress['current_streak'])->toBe(2);
});

test('misses before the go-live date never reset progress', function () {
    $group = challengeGroup(durationDays: 8, startedDaysAgo: 5);
    $user = User::factory()->create();
    joinAsApprovedMember($group, $user);
    completeDay($group, $user, 1);
    completeDay($group, $user, 2);
    completeDay($group, $user, 4);
    completeDay($group, $user, 5);
    config(['challenge.recalibration_starts_on' => today()->subDays(2)->toDateString()]);

    $progress = app(ChallengeProgress::class)->forMember($group, $user);

    expect($progress['reset_count'])->toBe(0);
});

test('the command records a reset once and tells the member and their partner', function () {
    Notification::fake();
    $group = challengeGroup(durationDays: 5, startedDaysAgo: 3);
    $partner = createApprovedManager($group);
    $user = User::factory()->create();
    $membership = joinAsApprovedMember($group, $user);
    $membership->partner_id = $partner->id;
    $membership->save();
    completeDay($group, $user, 1);
    completeDay($group, $user, 2);

    $this->artisan('progress:record-resets')->assertSuccessful();
    $this->artisan('progress:record-resets')->assertSuccessful();

    expect(ProgressReset::query()->count())->toBe(1);
    Notification::assertSentToTimes($user, ChallengeAlert::class, 1);
    Notification::assertSentToTimes($partner, ChallengeAlert::class, 1);
});

test('the command ignores members who had no progress to lose', function () {
    Notification::fake();
    $group = challengeGroup(durationDays: 5, startedDaysAgo: 3);
    $user = User::factory()->create();
    joinAsApprovedMember($group, $user);

    $this->artisan('progress:record-resets')->assertSuccessful();

    expect(ProgressReset::query()->count())->toBe(0);
    Notification::assertNothingSent();
});

test('the command does not reset partners who manage the group', function () {
    Notification::fake();
    $group = challengeGroup(durationDays: 5, startedDaysAgo: 3);
    $manager = createApprovedManager($group);
    completeDay($group, $manager, 1);

    $this->artisan('progress:record-resets')->assertSuccessful();

    expect(ProgressReset::query()->count())->toBe(0);
});

test('older misses are recorded silently', function () {
    Notification::fake();
    $group = challengeGroup(durationDays: 6, startedDaysAgo: 4);
    $user = User::factory()->create();
    joinAsApprovedMember($group, $user);
    completeDay($group, $user, 1);
    completeDay($group, $user, 3);
    completeDay($group, $user, 4);

    $this->artisan('progress:record-resets')->assertSuccessful();

    expect(ProgressReset::query()->count())->toBe(1);
    Notification::assertNothingSent();
});
