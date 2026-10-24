<?php

use App\Models\User;

test('guests can visit home without any groups of their own', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('home/index')
            ->where('yourGroups', [])
        );
});

test('users who have not completed onboarding are redirected to it', function () {
    $user = User::factory()->pendingOnboarding()->create();
    $this->actingAs($user);

    $response = $this->get(route('home'));

    $response->assertRedirect(route('onboarding.edit'));
});

test('onboarded users can visit home', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('home'));

    $response->assertOk();
});

test('the giving banner is shown once after signing in', function () {
    $user = User::factory()->create();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->get(route('home'))
        ->assertInertia(fn ($page) => $page->where('showGiveBanner', true));

    $this->get(route('home'))
        ->assertInertia(fn ($page) => $page->where('showGiveBanner', false));
});

test('the giving banner is not shown without signing in', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('home'))
        ->assertInertia(fn ($page) => $page->where('showGiveBanner', false));
});

test('the give page is shown to signed in users and not to guests', function () {
    $this->get(route('give'))->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create())
        ->get(route('give'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('give/index'));
});

test('admins can open the give page too', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('give'))
        ->assertOk();
});
