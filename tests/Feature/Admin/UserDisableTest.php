<?php

use App\Models\User;

test('non-admins cannot disable users', function () {
    $this->actingAs(User::factory()->create());
    $target = User::factory()->create();

    $this->put(route('admin.users.update-disabled', $target), ['disabled' => true])->assertForbidden();

    expect($target->fresh()->isDisabled())->toBeFalse();
});

test('an admin can disable and re-enable a user', function () {
    $this->actingAs(User::factory()->admin()->create());
    $target = User::factory()->create();

    $this->put(route('admin.users.update-disabled', $target), ['disabled' => true])
        ->assertRedirect()
        ->assertInertiaFlash('toast');
    expect($target->fresh()->isDisabled())->toBeTrue();

    $this->put(route('admin.users.update-disabled', $target), ['disabled' => false]);
    expect($target->fresh()->isDisabled())->toBeFalse();
});

test('an admin cannot disable their own account', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $this->put(route('admin.users.update-disabled', $admin), ['disabled' => true])
        ->assertSessionHasErrors('disabled');

    expect($admin->fresh()->isDisabled())->toBeFalse();
});

test('a disabled user is signed out on their next request', function () {
    $user = User::factory()->create(['disabled_at' => now()]);

    $this->actingAs($user)->get(route('home'))->assertRedirect(route('login'));

    $this->assertGuest();
});

test('a disabled user cannot sign in', function () {
    $user = User::factory()->create(['disabled_at' => now()]);

    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password']);
    $this->get(route('home'));

    $this->assertGuest();
});

test('a re-enabled user can use the app again', function () {
    $user = User::factory()->create(['disabled_at' => null]);

    $this->actingAs($user)->get(route('home'))->assertOk();
});
