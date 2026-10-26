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

test('members see a banner for each active user whose birthday is today', function () {
    $this->travelTo(now()->setDate(2026, 1, 20));

    $celebrant = User::factory()->create(['name' => 'Tolulope Olaleru', 'birthday_day' => 20, 'birthday_month' => 1]);
    User::factory()->create(['birthday_day' => 21, 'birthday_month' => 1]);
    User::factory()->create(['birthday_day' => 20, 'birthday_month' => 2]);
    $disabled = User::factory()->create(['birthday_day' => 20, 'birthday_month' => 1]);
    $disabled->disabled_at = now();
    $disabled->save();

    $this->actingAs(User::factory()->create())
        ->get(route('home'))
        ->assertInertia(fn ($page) => $page
            ->has('birthdays', 1)
            ->where('birthdays.0.id', $celebrant->id)
            ->where('birthdays.0.date', 'January 20th')
            ->where('birthdays.0.is_you', false)
        );
});

test('the birthday user is flagged so they get a personal greeting', function () {
    $user = User::factory()->create(['birthday_day' => today()->day, 'birthday_month' => today()->month]);

    $this->actingAs($user)
        ->get(route('home'))
        ->assertInertia(fn ($page) => $page->where('birthdays.0.is_you', true));
});

test('guests do not see birthdays', function () {
    User::factory()->create(['birthday_day' => today()->day, 'birthday_month' => today()->month]);

    $this->get(route('home'))
        ->assertInertia(fn ($page) => $page->where('birthdays', []));
});
