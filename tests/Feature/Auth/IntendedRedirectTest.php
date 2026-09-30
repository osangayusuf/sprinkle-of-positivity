<?php

use App\Models\User;

test('logging in from a guest prompt returns the user to the page they were on', function () {
    $user = User::factory()->create();

    $this->get(route('login', ['intended' => '/groups/some-group/verse']))->assertOk();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(url('/groups/some-group/verse'));
});

test('the intended path cannot send users to another site', function (string $intended) {
    $user = User::factory()->create();

    $this->get(route('login', ['intended' => $intended]));

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('home'));
})->with([
    'absolute url' => 'https://evil.example/phish',
    'protocol-relative url' => '//evil.example/phish',
    'backslash trick' => '/\\evil.example/phish',
]);

test('completing onboarding after registering returns to the page the guest was on', function () {
    $user = User::factory()->pendingOnboarding()->create();
    $this->actingAs($user);
    session()->put('url.intended', url('/groups/some-group'));

    $this->patch(route('onboarding.update'), [
        'name' => 'New Member',
        'whatsapp_number' => '+2348000000000',
        'goals' => ['Know the Bible better'],
        'birthday_day' => 1,
        'birthday_month' => 1,
    ])->assertRedirect(url('/groups/some-group'));
});
