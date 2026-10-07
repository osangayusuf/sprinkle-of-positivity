<?php

use App\Models\Group;
use App\Models\User;

test('a manager sees all of one participant\'s insights across days, newest day first', function () {
    $group = challengeGroup(durationDays: 5, startedDaysAgo: 3);
    $manager = createApprovedManager($group);
    $member = createApprovedMember($group);
    $other = createApprovedMember($group);
    completeDay($group, $member, 1);
    completeDay($group, $member, 3);
    completeDay($group, $other, 2);

    $this->actingAs($manager)
        ->get(route('groups.members.insights', [$group, $member]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('groups/member-insights')
            ->where('member.id', $member->id)
            ->has('insights', 2)
            ->where('insights.0.verse.date', $group->starts_on->copy()->addDays(2)->toDateString())
            ->where('insights.1.verse.date', $group->starts_on->toDateString()));
});

test('an admin can open a participant\'s insights', function () {
    $group = challengeGroup(durationDays: 5, startedDaysAgo: 3);
    $member = createApprovedMember($group);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('groups.members.insights', [$group, $member]))
        ->assertOk();
});

test('regular members and other groups\' managers cannot open it', function () {
    $group = challengeGroup(durationDays: 5, startedDaysAgo: 3);
    $member = createApprovedMember($group);
    $outsideManager = createApprovedManager(Group::factory()->create());

    $this->actingAs(createApprovedMember($group))
        ->get(route('groups.members.insights', [$group, $member]))
        ->assertForbidden();
    $this->actingAs($outsideManager)
        ->get(route('groups.members.insights', [$group, $member]))
        ->assertForbidden();
});

test('someone who is not an approved member of the group is a 404', function () {
    $group = challengeGroup(durationDays: 5, startedDaysAgo: 3);
    $manager = createApprovedManager($group);

    $this->actingAs($manager)
        ->get(route('groups.members.insights', [$group, User::factory()->create()]))
        ->assertNotFound();
});
