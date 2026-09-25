<?php

use App\Models\Role;
use App\Models\User;

test('non-admins cannot view or manage users', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('admin.users.index'))->assertForbidden();

    $this->put(route('admin.users.update-role', $target), [
        'role' => Role::ADMIN,
    ])->assertForbidden();
});

test('an admin can view the users list', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->count(2)->create();
    $this->actingAs($admin);

    $response = $this->get(route('admin.users.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->has('users', 3));
});

test('an admin can promote a member to admin', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->create();
    $this->actingAs($admin);

    $response = $this->put(route('admin.users.update-role', $member), [
        'role' => Role::ADMIN,
    ]);

    $response->assertRedirect();
    $response->assertInertiaFlash('toast');
    expect($member->fresh()->hasRole(Role::ADMIN))->toBeTrue();
});

test('an admin can demote another admin to member', function () {
    $admin = User::factory()->admin()->create();
    $otherAdmin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $this->put(route('admin.users.update-role', $otherAdmin), [
        'role' => Role::MEMBER,
    ]);

    expect($otherAdmin->fresh()->hasRole(Role::ADMIN))->toBeFalse();
});

test('an admin cannot demote themselves', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $this->put(route('admin.users.update-role', $admin), [
        'role' => Role::MEMBER,
    ])->assertInvalid('role');

    expect($admin->fresh()->hasRole(Role::ADMIN))->toBeTrue();
});
