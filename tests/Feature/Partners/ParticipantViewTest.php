<?php

use App\Models\Group;
use App\Models\User;
use App\Notifications\ChallengeAlert;
use Illuminate\Support\Facades\Notification;

function assignedParticipant(User $partner, Group $group): array
{
    $user = User::factory()->create();
    $membership = joinAsApprovedMember($group, $user);
    $membership->partner_id = $partner->id;
    $membership->save();

    return [$user, $membership];
}

test('a partner sees only their own participants with their status', function () {
    $group = challengeGroup(durationDays: 5, startedDaysAgo: 2);
    $partner = createApprovedManager($group);
    $other = createApprovedManager($group);
    [$done, $doneMembership] = assignedParticipant($partner, $group);
    [$behind] = assignedParticipant($partner, $group);
    assignedParticipant($other, $group);
    completeDay($group, $done, 1);
    completeDay($group, $done, 2);
    completeDay($group, $done, 3);

    $this->actingAs($partner)
        ->get(route('partner.participants'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('partner/participants')
            ->has('participants', 2)
            ->where('participants', fn ($participants) => $participants->firstWhere('id', $doneMembership->id)['status'] === 'on_track'
                && $participants->firstWhere('user.id', $behind->id)['status'] === 'lagging'));
});

test('members and unapproved partners cannot open the partner view', function () {
    $this->actingAs(User::factory()->create())->get(route('partner.participants'))->assertForbidden();
});

test('an admin sees every partner\'s participants', function () {
    $group = challengeGroup(durationDays: 5, startedDaysAgo: 2);
    assignedParticipant(createApprovedManager($group), $group);
    assignedParticipant(createApprovedManager($group), $group);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('partner.participants'))
        ->assertInertia(fn ($page) => $page->has('participants', 2)->where('isAdmin', true));
});

test('a partner can nudge a participant once a day', function () {
    Notification::fake();
    $group = challengeGroup(durationDays: 5, startedDaysAgo: 2);
    $partner = createApprovedManager($group);
    [$participant, $membership] = assignedParticipant($partner, $group);

    $this->actingAs($partner)->post(route('partner.participants.nudge', $membership))->assertRedirect();
    $this->actingAs($partner)->post(route('partner.participants.nudge', $membership))->assertRedirect();

    Notification::assertSentToTimes($participant, ChallengeAlert::class, 1);
});

test('a partner cannot nudge someone else\'s participant', function () {
    $group = challengeGroup(durationDays: 5, startedDaysAgo: 2);
    $owner = createApprovedManager($group);
    $other = createApprovedManager($group);
    [, $membership] = assignedParticipant($owner, $group);

    $this->actingAs($other)->post(route('partner.participants.nudge', $membership))->assertForbidden();
});
