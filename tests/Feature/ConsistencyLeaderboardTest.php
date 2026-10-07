<?php

use App\Models\Group;
use App\Models\User;

function participantIn(Group $group, ?User $partner = null): User
{
    $user = User::factory()->create();
    $membership = joinAsApprovedMember($group, $user);

    if ($partner) {
        $membership->partner_id = $partner->id;
        $membership->save();
    }

    return $user;
}

test('the consistency leaderboard ranks participants by current streak', function () {
    $group = challengeGroup(durationDays: 5, startedDaysAgo: 3);
    $long = participantIn($group);
    $short = participantIn($group);
    foreach ([1, 2, 3] as $day) {
        completeDay($group, $long, $day);
    }
    completeDay($group, $short, 3);

    $this->actingAs($short)
        ->get(route('leaderboard.consistency'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('entries.0.name', $long->name)
            ->where('entries.0.current_streak', 3)
            ->where('entries.1.is_me', true));
});

test('partners are ranked by the share of their participants keeping up', function () {
    $group = challengeGroup(durationDays: 5, startedDaysAgo: 2);
    $strong = createApprovedManager($group);
    $weak = createApprovedManager($group);
    $keepingUp = participantIn($group, $strong);
    participantIn($group, $weak);
    completeDay($group, $keepingUp, 1);
    completeDay($group, $keepingUp, 2);
    completeDay($group, $keepingUp, 3);

    $this->actingAs($keepingUp)
        ->get(route('leaderboard.consistency'))
        ->assertInertia(fn ($page) => $page
            ->where('partners.0.name', $strong->name)
            ->where('partners.0.percent', 100)
            ->where('partners.1.percent', 0));
});

test('regular members never see who is lagging', function () {
    $group = challengeGroup(durationDays: 5, startedDaysAgo: 2);
    participantIn($group);

    $this->actingAs(User::factory()->create())
        ->get(route('leaderboard.consistency'))
        ->assertInertia(fn ($page) => $page->where('lagging', null));
});

test('a partner sees only their own lagging participants', function () {
    $group = challengeGroup(durationDays: 5, startedDaysAgo: 2);
    $partner = createApprovedManager($group);
    $other = createApprovedManager($group);
    $mine = participantIn($group, $partner);
    participantIn($group, $other);

    $this->actingAs($partner)
        ->get(route('leaderboard.consistency'))
        ->assertInertia(fn ($page) => $page
            ->has('lagging', 1)
            ->where('lagging.0.name', $mine->name));
});

test('an admin sees every lagging participant', function () {
    $group = challengeGroup(durationDays: 5, startedDaysAgo: 2);
    participantIn($group, createApprovedManager($group));
    participantIn($group, createApprovedManager($group));

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('leaderboard.consistency'))
        ->assertInertia(fn ($page) => $page->has('lagging', 2));
});
