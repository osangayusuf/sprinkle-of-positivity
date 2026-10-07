<?php

use App\Enums\GroupMembershipRole;
use App\Enums\GroupMembershipStatus;
use App\Enums\GroupStatus;
use App\Models\Group;
use App\Models\GroupVerse;
use App\Models\Quiz;
use App\Models\User;

test('non-admins cannot access the admin groups area', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('admin.groups.index'))->assertForbidden();
    $this->get(route('admin.groups.create'))->assertForbidden();
});

test('non-admins cannot edit, update, or delete a group', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create();
    $this->actingAs($user);

    $this->get(route('admin.groups.edit', $group))->assertForbidden();
    $this->put(route('admin.groups.update', $group), ['name' => 'X'])->assertForbidden();
    $this->delete(route('admin.groups.destroy', $group))->assertForbidden();
});

test('admins can create a group and appoint a manager', function () {
    $admin = User::factory()->admin()->create();
    $manager = User::factory()->partner()->create();
    $this->actingAs($admin);

    $response = $this->post(route('admin.groups.store'), [
        'name' => '60-Day Bible Study Challenge',
        'purpose' => 'A daily challenge to grow in faith together.',
        'duration_days' => 60,
        'starts_on' => today()->toDateString(),
        'manager_ids' => [$manager->id],
    ]);

    $response->assertRedirect(route('admin.groups.index'));

    $group = Group::query()->sole();
    expect($group->name)->toBe('60-Day Bible Study Challenge');
    expect($group->slug)->toBe('60-day-bible-study-challenge');
    expect($group->created_by)->toBe($admin->id);

    $membership = $group->memberships()->sole();
    expect($membership->user_id)->toBe($manager->id);
    expect($membership->role)->toBe(GroupMembershipRole::Manager);
    expect($membership->status)->toBe(GroupMembershipStatus::Approved);
});

test('admins can update a group\'s details and status', function () {
    $admin = User::factory()->admin()->create();
    $group = Group::factory()->create(['name' => 'Old Name']);
    $manager = createApprovedManager($group);
    $this->actingAs($admin);

    $response = $this->put(route('admin.groups.update', $group), [
        'name' => 'New Name',
        'purpose' => 'Updated purpose.',
        'duration_days' => 30,
        'starts_on' => today()->toDateString(),
        'status' => GroupStatus::Archived->value,
        'manager_ids' => [$manager->id],
    ]);

    $response->assertRedirect(route('admin.groups.index'));
    $response->assertInertiaFlash('toast');

    $group->refresh();
    expect($group->name)->toBe('New Name');
    expect($group->status)->toBe(GroupStatus::Archived);
});

test('admins can reassign a group\'s manager to an existing member', function () {
    $admin = User::factory()->admin()->create();
    $group = Group::factory()->create();
    $oldManager = createApprovedManager($group);
    $newManager = User::factory()->partner()->create();
    joinAsApprovedMember($group, $newManager);
    $this->actingAs($admin);

    $this->put(route('admin.groups.update', $group), [
        'name' => $group->name,
        'purpose' => $group->purpose,
        'status' => GroupStatus::Active->value,
        'manager_ids' => [$newManager->id],
    ])->assertRedirect(route('admin.groups.index'));

    expect($group->fresh()->managers()->pluck('users.id')->all())->toBe([$newManager->id]);
    $oldMembership = $group->memberships()->where('user_id', $oldManager->id)->sole();
    expect($oldMembership->role)->toBe(GroupMembershipRole::Member);
});

test('admins can reassign a group\'s manager to a user with no prior membership', function () {
    $admin = User::factory()->admin()->create();
    $group = Group::factory()->create();
    $oldManager = createApprovedManager($group);
    $newManager = User::factory()->partner()->create();
    $this->actingAs($admin);

    $this->put(route('admin.groups.update', $group), [
        'name' => $group->name,
        'purpose' => $group->purpose,
        'status' => GroupStatus::Active->value,
        'manager_ids' => [$newManager->id],
    ])->assertRedirect(route('admin.groups.index'));

    $newMembership = $group->memberships()->where('user_id', $newManager->id)->sole();
    expect($newMembership->role)->toBe(GroupMembershipRole::Manager);
    expect($newMembership->status)->toBe(GroupMembershipStatus::Approved);

    $oldMembership = $group->memberships()->where('user_id', $oldManager->id)->sole();
    expect($oldMembership->role)->toBe(GroupMembershipRole::Member);
});

test('reassigning the manager to the current manager is a no-op', function () {
    $admin = User::factory()->admin()->create();
    $group = Group::factory()->create();
    $manager = createApprovedManager($group);
    $this->actingAs($admin);

    $this->put(route('admin.groups.update', $group), [
        'name' => $group->name,
        'purpose' => $group->purpose,
        'status' => GroupStatus::Active->value,
        'manager_ids' => [$manager->id],
    ])->assertRedirect(route('admin.groups.index'));

    expect($group->memberships()->count())->toBe(1);
    expect($group->fresh()->managers()->pluck('users.id')->all())->toBe([$manager->id]);
});

test('admins can delete a group, cascading its verses and quizzes', function () {
    $admin = User::factory()->admin()->create();
    $group = Group::factory()->create();
    $verse = GroupVerse::factory()->for($group)->create();
    Quiz::factory()->for($group)->for($verse, 'groupVerse')->create();
    $this->actingAs($admin);

    $response = $this->delete(route('admin.groups.destroy', $group));

    $response->assertRedirect();
    $response->assertInertiaFlash('toast');

    expect(Group::query()->count())->toBe(0);
    expect(GroupVerse::query()->count())->toBe(0);
    expect(Quiz::query()->count())->toBe(0);
});

test('admins can create a private group', function () {
    $this->actingAs(User::factory()->admin()->create());

    $this->post(route('admin.groups.store'), [
        'name' => 'Leaders Circle',
        'purpose' => 'For group leaders.',
        'is_private' => '1',
        'manager_ids' => [User::factory()->partner()->create()->id],
    ])->assertRedirect(route('admin.groups.index'));

    expect(Group::query()->sole()->is_private)->toBeTrue();
});

test('admins can make a private group public by unticking the private box', function () {
    $group = Group::factory()->private()->create();
    $manager = createApprovedManager($group);
    $this->actingAs(User::factory()->admin()->create());

    $this->put(route('admin.groups.update', $group), [
        'name' => $group->name,
        'purpose' => $group->purpose,
        'status' => GroupStatus::Active->value,
        'manager_ids' => [$manager->id],
    ])->assertRedirect(route('admin.groups.index'));

    expect($group->refresh()->is_private)->toBeFalse();
});
