<?php

use App\Enums\GroupMembershipRole;
use App\Enums\GroupMembershipStatus;
use App\Models\Group;
use App\Models\GroupMembership;
use App\Models\User;

test('a user can apply to join a group and lands in pending status', function () {
    $group = Group::factory()->create();
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post(route('groups.apply', $group), [
        'code_of_conduct_agreed' => true,
    ]);

    $response->assertRedirect(route('groups.show', $group));

    $membership = GroupMembership::query()->sole();
    expect($membership->user_id)->toBe($user->id);
    expect($membership->status)->toBe(GroupMembershipStatus::Pending);
    expect($membership->role)->toBe(GroupMembershipRole::Member);
    expect($membership->code_of_conduct_accepted_at)->not->toBeNull();
});

test('applying without accepting the code of conduct fails validation', function () {
    $group = Group::factory()->create();
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post(route('groups.apply', $group), [
        'code_of_conduct_agreed' => false,
    ]);

    $response->assertSessionHasErrors('code_of_conduct_agreed');
    expect(GroupMembership::query()->count())->toBe(0);
});

test('a user cannot apply to a group they already have a membership for', function () {
    $group = Group::factory()->create();
    $user = User::factory()->create();
    $user->groups()->attach($group->id, [
        'role' => GroupMembershipRole::Member->value,
        'status' => GroupMembershipStatus::Pending->value,
        'applied_at' => now(),
    ]);
    $this->actingAs($user);

    $this->post(route('groups.apply', $group), ['code_of_conduct_agreed' => true])
        ->assertForbidden();
});

test('a manager can approve a pending application', function () {
    $group = Group::factory()->create();
    $manager = createApprovedManager($group);

    $applicant = User::factory()->create();
    $membership = new GroupMembership;
    $membership->group_id = $group->id;
    $membership->user_id = $applicant->id;
    $membership->role = GroupMembershipRole::Member;
    $membership->status = GroupMembershipStatus::Pending;
    $membership->applied_at = now();
    $membership->save();

    $this->actingAs($manager)
        ->patch(route('groups.applications.update', [$group, $membership]), [
            'decision' => 'approved',
        ])
        ->assertRedirect();

    expect($membership->refresh()->status)->toBe(GroupMembershipStatus::Approved);
    expect($membership->decided_by)->toBe($manager->id);
    expect($membership->decided_at)->not->toBeNull();
});

test('a manager can reject a pending application', function () {
    $group = Group::factory()->create();
    $manager = createApprovedManager($group);

    $applicant = User::factory()->create();
    $membership = new GroupMembership;
    $membership->group_id = $group->id;
    $membership->user_id = $applicant->id;
    $membership->role = GroupMembershipRole::Member;
    $membership->status = GroupMembershipStatus::Pending;
    $membership->applied_at = now();
    $membership->save();

    $this->actingAs($manager)
        ->patch(route('groups.applications.update', [$group, $membership]), [
            'decision' => 'rejected',
        ])
        ->assertRedirect();

    expect($membership->refresh()->status)->toBe(GroupMembershipStatus::Rejected);
});

test('a manager can remove an approved member', function () {
    $group = Group::factory()->create();
    $manager = createApprovedManager($group);

    $member = User::factory()->create();
    $membership = new GroupMembership;
    $membership->group_id = $group->id;
    $membership->user_id = $member->id;
    $membership->role = GroupMembershipRole::Member;
    $membership->status = GroupMembershipStatus::Approved;
    $membership->applied_at = now();
    $membership->decided_at = now();
    $membership->save();

    $this->actingAs($manager)
        ->delete(route('groups.members.destroy', [$group, $membership]))
        ->assertRedirect();

    expect($membership->refresh()->status)->toBe(GroupMembershipStatus::Removed);
});

test('a non-manager cannot approve, reject, or remove members', function () {
    $group = Group::factory()->create();
    $outsider = User::factory()->create();

    $applicant = User::factory()->create();
    $membership = new GroupMembership;
    $membership->group_id = $group->id;
    $membership->user_id = $applicant->id;
    $membership->role = GroupMembershipRole::Member;
    $membership->status = GroupMembershipStatus::Pending;
    $membership->applied_at = now();
    $membership->save();

    $this->actingAs($outsider);

    $this->patch(route('groups.applications.update', [$group, $membership]), [
        'decision' => 'approved',
    ])->assertForbidden();

    $this->delete(route('groups.members.destroy', [$group, $membership]))
        ->assertForbidden();

    expect($membership->refresh()->status)->toBe(GroupMembershipStatus::Pending);
});

test('a membership from another group cannot be decided through this group', function () {
    $groupA = Group::factory()->create();
    $groupB = Group::factory()->create();
    $manager = createApprovedManager($groupA);

    $applicant = User::factory()->create();
    $membership = new GroupMembership;
    $membership->group_id = $groupB->id;
    $membership->user_id = $applicant->id;
    $membership->role = GroupMembershipRole::Member;
    $membership->status = GroupMembershipStatus::Pending;
    $membership->applied_at = now();
    $membership->save();

    $this->actingAs($manager)
        ->patch(route('groups.applications.update', [$groupA, $membership]), [
            'decision' => 'approved',
        ])
        ->assertNotFound();
});
