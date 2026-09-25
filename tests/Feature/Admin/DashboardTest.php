<?php

use App\Models\User;

test('non-admins cannot view the admin dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('admin.dashboard'))->assertForbidden();
});

test('admins can view the admin dashboard', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $this->get(route('admin.dashboard'))->assertOk();
});

test('isAdmin is shared with the frontend for admins and members', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertInertia(fn ($page) => $page->where('auth.isAdmin', true));

    $this->actingAs($member)
        ->get(route('home'))
        ->assertInertia(fn ($page) => $page->where('auth.isAdmin', false));
});
