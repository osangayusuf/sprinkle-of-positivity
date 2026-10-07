<?php

use App\Models\Group;
use App\Models\User;
use App\Notifications\ChallengeAlert;
use Illuminate\Support\Facades\Notification;

test('participants who have not posted today are reminded and their partner is told', function () {
    Notification::fake();
    $group = challengeGroup(durationDays: 5, startedDaysAgo: 2);
    $partner = createApprovedManager($group);
    $pending = User::factory()->create();
    $done = User::factory()->create();
    foreach ([$pending, $done] as $user) {
        $membership = joinAsApprovedMember($group, $user);
        $membership->partner_id = $partner->id;
        $membership->save();
    }
    completeDay($group, $done, 3);

    $this->artisan('reminders:send')->assertSuccessful();

    Notification::assertSentToTimes($pending, ChallengeAlert::class, 1);
    Notification::assertNotSentTo($done, ChallengeAlert::class);
    Notification::assertSentToTimes($partner, ChallengeAlert::class, 1);
});

test('nobody is reminded in a group with no verse today', function () {
    Notification::fake();
    $group = challengeGroup(durationDays: 5, startedDaysAgo: 2, daysWithoutVerse: [3]);
    $user = User::factory()->create();
    joinAsApprovedMember($group, $user);

    $this->artisan('reminders:send')->assertSuccessful();

    Notification::assertNothingSent();
});

test('groups without a challenge are left alone', function () {
    Notification::fake();
    $group = Group::factory()->create();
    joinAsApprovedMember($group, User::factory()->create());

    $this->artisan('reminders:send')->assertSuccessful();

    Notification::assertNothingSent();
});

test('the evening reminder is scheduled for 6 PM West Africa Time', function () {
    $this->artisan('schedule:list')
        ->expectsOutputToContain('0 18 * * *')
        ->assertSuccessful();
});
