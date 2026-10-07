<?php

use App\Actions\DecideGroupApplication;
use App\Enums\GroupMembershipStatus;
use App\Enums\GroupStatus;
use App\Models\Group;
use App\Models\GroupMembership;
use App\Models\User;
use App\Notifications\ChallengeAlert;
use Illuminate\Support\Facades\Notification;

function pendingApplicant(Group $group): GroupMembership
{
    $applicant = User::factory()->create();
    $applicant->groups()->attach($group->id, ['role' => 'member', 'status' => 'pending', 'applied_at' => now()]);

    return $applicant->groupMemberships()->where('group_id', $group->id)->sole();
}

test('an approved participant is assigned to the partner with the fewest participants', function () {
    Notification::fake();
    $group = Group::factory()->create();
    $busy = createApprovedManager($group);
    $free = createApprovedManager($group);
    $existing = joinAsApprovedMember($group, User::factory()->create());
    $existing->partner_id = $busy->id;
    $existing->save();

    $membership = pendingApplicant($group);
    app(DecideGroupApplication::class)->handle($membership, GroupMembershipStatus::Approved, $busy);

    expect($membership->fresh()->partner_id)->toBe($free->id);
    Notification::assertSentTo($free, ChallengeAlert::class);
});

test('rejecting an applicant assigns no partner', function () {
    $group = Group::factory()->create();
    $partner = createApprovedManager($group);

    $membership = pendingApplicant($group);
    app(DecideGroupApplication::class)->handle($membership, GroupMembershipStatus::Rejected, $partner);

    expect($membership->fresh()->partner_id)->toBeNull();
});

test('a participant with no partner to take them stays unassigned and admins are told', function () {
    Notification::fake();
    $admin = User::factory()->admin()->create();
    $group = Group::factory()->create();

    $membership = pendingApplicant($group);
    app(DecideGroupApplication::class)->handle($membership, GroupMembershipStatus::Approved, $admin);

    expect($membership->fresh()->partner_id)->toBeNull();
    Notification::assertSentTo($admin, ChallengeAlert::class);
});

test('a partner can decline a participant who then moves to another partner', function () {
    Notification::fake();
    $group = Group::factory()->create();
    $first = createApprovedManager($group);
    $second = createApprovedManager($group);
    $membership = joinAsApprovedMember($group, User::factory()->create());
    $membership->partner_id = $first->id;
    $membership->save();

    $this->actingAs($first)
        ->post(route('partner.participants.reject', $membership), ['reason' => 'At capacity right now.'])
        ->assertRedirect();

    expect($membership->fresh()->partner_id)->toBe($second->id);
});

test('declining the only partner leaves the participant for an admin to place', function () {
    Notification::fake();
    $admin = User::factory()->admin()->create();
    $group = Group::factory()->create();
    $only = createApprovedManager($group);
    $membership = joinAsApprovedMember($group, User::factory()->create());
    $membership->partner_id = $only->id;
    $membership->save();

    $this->actingAs($only)
        ->post(route('partner.participants.reject', $membership), ['reason' => 'Too many.']);

    expect($membership->fresh()->partner_id)->toBeNull();
    Notification::assertSentTo($admin, ChallengeAlert::class);
});

test('declining a participant needs a reason', function () {
    $group = Group::factory()->create();
    $partner = createApprovedManager($group);
    $membership = joinAsApprovedMember($group, User::factory()->create());
    $membership->partner_id = $partner->id;
    $membership->save();

    $this->actingAs($partner)
        ->post(route('partner.participants.reject', $membership), [])
        ->assertSessionHasErrors('reason');

    expect($membership->fresh()->partner_id)->toBe($partner->id);
});

test('a partner cannot decline someone else\'s participant', function () {
    $group = Group::factory()->create();
    $owner = createApprovedManager($group);
    $other = createApprovedManager($group);
    $membership = joinAsApprovedMember($group, User::factory()->create());
    $membership->partner_id = $owner->id;
    $membership->save();

    $this->actingAs($other)
        ->post(route('partner.participants.reject', $membership), ['reason' => 'Not mine.'])
        ->assertForbidden();
});

test('an admin can reassign a participant to a partner of the group', function () {
    Notification::fake();
    $group = Group::factory()->create();
    $first = createApprovedManager($group);
    $second = createApprovedManager($group);
    $membership = joinAsApprovedMember($group, User::factory()->create());
    $membership->partner_id = $first->id;
    $membership->save();

    $this->actingAs(User::factory()->admin()->create())
        ->put(route('admin.groups.assignments.update', [$group, $membership]), ['partner_id' => $second->id])
        ->assertRedirect();

    expect($membership->fresh()->partner_id)->toBe($second->id);
    Notification::assertSentTo($second, ChallengeAlert::class);
});

test('an admin cannot assign a participant to someone who is not a partner of the group', function () {
    $group = Group::factory()->create();
    $membership = joinAsApprovedMember($group, User::factory()->create());
    $outsider = User::factory()->partner()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->put(route('admin.groups.assignments.update', [$group, $membership]), ['partner_id' => $outsider->id])
        ->assertUnprocessable();

    expect($membership->fresh()->partner_id)->toBeNull();
});

test('only admins see the assignments screen', function () {
    $group = Group::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('admin.groups.assignments.index', $group))
        ->assertForbidden();

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.groups.assignments.index', $group))
        ->assertOk();
});

test('only approved partners can be appointed as group managers', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    foreach ([User::factory()->create(), User::factory()->pendingPartner()->create()] as $candidate) {
        $this->post(route('admin.groups.store'), [
            'name' => 'Cohort',
            'purpose' => 'Study together.',
            'manager_ids' => [$candidate->id],
        ])->assertSessionHasErrors('manager_ids.0');
    }

    expect(Group::query()->count())->toBe(0);
});

test('a group needs at least one partner', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->post(route('admin.groups.store'), ['name' => 'Cohort', 'purpose' => 'Study together.', 'manager_ids' => []])
        ->assertSessionHasErrors('manager_ids');
});

test('removing a partner from a group hands their participants to the others', function () {
    Notification::fake();
    $group = Group::factory()->create();
    $leaving = createApprovedManager($group);
    $staying = createApprovedManager($group);
    $membership = joinAsApprovedMember($group, User::factory()->create());
    $membership->partner_id = $leaving->id;
    $membership->save();

    $this->actingAs(User::factory()->admin()->create())
        ->put(route('admin.groups.update', $group), [
            'name' => $group->name,
            'purpose' => $group->purpose,
            'status' => GroupStatus::Active->value,
            'manager_ids' => [$staying->id],
        ])->assertRedirect(route('admin.groups.index'));

    expect($membership->fresh()->partner_id)->toBe($staying->id)
        ->and($group->managers()->pluck('users.id')->all())->toBe([$staying->id]);
});

test('adding a partner to a group places participants who had none', function () {
    Notification::fake();
    $group = Group::factory()->create();
    $existing = createApprovedManager($group);
    $waiting = joinAsApprovedMember($group, User::factory()->create());
    $newPartner = User::factory()->partner()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->put(route('admin.groups.update', $group), [
            'name' => $group->name,
            'purpose' => $group->purpose,
            'status' => GroupStatus::Active->value,
            'manager_ids' => [$existing->id, $newPartner->id],
        ]);

    expect($waiting->fresh()->partner_id)->toBeIn([$existing->id, $newPartner->id]);
});
